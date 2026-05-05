<?php
require_once 'connect.php';


$BLOOD_COMPATIBILITY = [
    
    'O-' => ['O-'],
    'O+' => ['O+', 'O-'],
    'A-' => ['O-', 'A-'],
    'A+' => ['O+', 'O-', 'A+', 'A-'],
    'B-' => ['O-', 'B-'],
    'B+' => ['O+', 'O-', 'B+', 'B-'],
    'AB-' => ['O-', 'A-', 'B-', 'AB-'],
    'AB+' => ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-']
];
function is_blood_compatible($donor_type, $recipient_type) {
    global $BLOOD_COMPATIBILITY;
    // Normalize blood types (trim spaces and uppercase) to prevent format mismatches
    $donor_type = trim(strtoupper($donor_type ?? ''));
    $recipient_type = trim(strtoupper($recipient_type ?? ''));
    $compatible_types = $BLOOD_COMPATIBILITY[$recipient_type] ?? [];
    return in_array($donor_type, $compatible_types);
}

function getDistance($lat1, $lon1, $lat2, $lon2) {
    // Check for valid coordinates
    if (empty($lat1) || empty($lon1) || empty($lat2) || empty($lon2)) {
        return 0; // Fallback for missing coordinates
    }
    
    // Earth's radius in kilometers
    $earth_radius_km = 6371;
    
    // Convert degrees to radians
    $lat1_rad = deg2rad($lat1);
    $lon1_rad = deg2rad($lon1);
    $lat2_rad = deg2rad($lat2);
    $lon2_rad = deg2rad($lon2);
    
    // Haversine formula
    $dlat = $lat2_rad - $lat1_rad;
    $dlon = $lon2_rad - $lon1_rad;
    
    $a = sin($dlat / 2) * sin($dlat / 2) + 
         cos($lat1_rad) * cos($lat2_rad) * 
         sin($dlon / 2) * sin($dlon / 2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $earth_radius_km * $c;
    
    return round($distance, 2);
}
function calculateScore($distance, $donations) {
    // Distance score: closer = higher (max 40 points)
    // 0-10 km = full points, 50+ km = 0 points
    $distance_score = max(0, (1 - ($distance / 50)) * 40);
    
    // Experience score: more donations = higher (max 30 points)
    // 1 donation = 2 points, 15+ donations = 30 points
    $experience_score = min(30, $donations * 2);
    
    // Base score (0-70)
    $base_score = $distance_score + $experience_score;
    
    // Final score (0-70)
    $final_score = min(100, round($base_score, 2));
    
    return $final_score;
}

function getDonorEligibility($donor, $health_data, $last_donation_date) {
    $status = 'eligible';
    $messages = [];

    // 1. Date-based condition (56 days gap)
    if ($last_donation_date) {
        $eligible_date = strtotime('+56 days', strtotime($last_donation_date));
        $today = strtotime(date('Y-m-d'));
        if ($today < $eligible_date) {
            $status = 'ineligible';
            $messages[] = '56-day donation interval not met.';
        }
    }

    // 2. Health vitals (from health_records)
    if ($health_data) {
        $gender = $donor['gender'] ?? 'M';
        $hem = (float)$health_data['hemoglobin'];
        if ($gender === 'F' && $hem < 12.5) {
            $status = 'ineligible';
            $messages[] = 'Hemoglobin too low for females.';
        } elseif ($gender === 'M' && $hem < 13.5) {
            $status = 'ineligible';
            $messages[] = 'Hemoglobin too low for males.';
        }
        
        if (strpos($health_data['blood_pressure'], '/') !== false) {
            list($systolic, $diastolic) = explode('/', $health_data['blood_pressure']);
            if ((int)$systolic > 180 || (int)$diastolic > 110) {
                $status = 'ineligible';
                $messages[] = 'Blood pressure too high.';
            }
        }
    }

    // 3. Comprehensive Health Records (from users table)
    $medical_condition = strtolower($donor['medical_condition'] ?? '');
    $medications = strtolower($donor['medications'] ?? '');
    $allergies = strtolower($donor['allergies'] ?? '');

    // Pregnancy
    if (strpos($medical_condition, 'pregnant') !== false) {
        $status = 'ineligible';
        $messages[] = 'Not allowed to donate during pregnancy.';
    }

    // Menstruation
    if (strpos($medical_condition, 'menstruat') !== false || strpos($medical_condition, 'period') !== false) {
        if (strpos($medical_condition, 'heavy') !== false || strpos($medical_condition, 'weak') !== false) {
            $status = 'restricted';
            $messages[] = 'Temporarily restricted due to heavy menstruation or weakness.';
        } else {
            if ($status === 'eligible') $status = 'restricted'; // Conditional
            $messages[] = 'Menstruation: Allow with caution (consult doctor if weak).';
        }
    }

    // Heavy medication
    if (strpos($medications, 'heavy') !== false || strpos($medications, 'antibiotic') !== false) {
        $status = 'restricted';
        $messages[] = 'Temporarily restricted due to heavy medication.';
    }

    // Allergy medications
    if (strpos($medications, 'antihistamine') !== false || strpos($medications, 'allergy') !== false) {
        if ($status === 'eligible') $status = 'restricted';
        $messages[] = 'Allergy medications: Allow with caution.';
    }

    // Allergies
    if (strpos($allergies, 'severe') !== false || strpos($allergies, 'anaphylaxis') !== false) {
        $status = 'ineligible';
        $messages[] = 'Not allowed to donate due to severe allergic history.';
    } elseif (strpos($allergies, 'dust') !== false || strpos($allergies, 'pollen') !== false || strpos($allergies, 'food') !== false) {
        // Generally safe, do not change status to ineligible
        $messages[] = 'Common allergies (dust/pollen/food) are generally safe.';
    }

    // Ensure ineligible takes precedence over restricted
    foreach ($messages as $msg) {
        if (strpos($msg, 'Not allowed') !== false || strpos($msg, 'too low') !== false || strpos($msg, 'too high') !== false || strpos($msg, 'not met') !== false) {
            $status = 'ineligible';
        }
    }

    return ['status' => $status, 'messages' => $messages];
}

function processDonors($donors_result, array $recipient, array &$matched_blood_types, $conn = null): array {
    $all_donors = [];
    $recipient_blood_type = trim(strtoupper($recipient['blood_type'] ?? ''));

    if (!$donors_result || $donors_result->num_rows === 0) {
        return $all_donors;
    }

    while ($donor = $donors_result->fetch_assoc()) {
        // Evaluate Comprehensive Eligibility
        if ($conn) {
            $donor_id = $donor['id'];
            
            // Get latest health record
            $health_res = $conn->query("SELECT * FROM health_records WHERE donor_id = $donor_id ORDER BY recorded_date DESC LIMIT 1");
            $health_data = ($health_res && $health_res->num_rows > 0) ? $health_res->fetch_assoc() : null;
            
            // Get last donation date
            $last_donation_res = $conn->query("SELECT donation_date FROM donations WHERE donor_id = $donor_id AND status = 'completed' ORDER BY donation_date DESC LIMIT 1");
            $last_donation_date = ($last_donation_res && $last_donation_res->num_rows > 0) ? $last_donation_res->fetch_assoc()['donation_date'] : null;

            $eligibility = getDonorEligibility($donor, $health_data, $last_donation_date);
            
            // Filter out ineligible or restricted donors
            if ($eligibility['status'] !== 'eligible') {
                continue; // Skip this donor, they are not eligible for matching!
            }
        }

        // Step 1: Check blood compatibility
        $donor_blood_type = trim(strtoupper($donor['blood_type'] ?? ''));
        $donor['is_compatible'] = is_blood_compatible($donor_blood_type, $recipient_blood_type);

        if ($donor['is_compatible'] && !in_array($donor['blood_type'], $matched_blood_types)) {
            $matched_blood_types[] = $donor['blood_type'];
        }

        // Step 2: Calculate distance (km) between donor and recipient
        $distance = getDistance(
            $donor['latitude'],
            $donor['longitude'],
            $recipient['latitude'],
            $recipient['longitude']
        );

        // Step 3: Calculate priority score (0-70)
        // calculateScore() handles missing coordinates (distance = 0 fallback)
        $priority_score = calculateScore($distance, $donor['total_donations'] ?? 0);

        $donor['distance_km']    = $distance;
        $donor['priority_score'] = $priority_score;

        $all_donors[] = $donor;
    }

    return $all_donors;
}
function sortDonors(array &$donors): void {
    usort($donors, function($a, $b) {
        // Level 1: Compatible donors always first
        if ($a['is_compatible'] && !$b['is_compatible']) return -1;
        if (!$a['is_compatible'] && $b['is_compatible']) return 1;

        // Level 2: Higher priority score first
        if ($a['priority_score'] != $b['priority_score']) {
            return ($a['priority_score'] > $b['priority_score']) ? -1 : 1;
        }

        // Level 3: More donations first (tiebreaker)
        return ($a['total_donations'] > $b['total_donations']) ? -1 : 1;
    });
}

/**
 * Sort donors by:
 * 1. Blood compatibility (compatible first)
 * 2. Priority score (higher first)
 * 3. Total donations (more experience first, tiebreaker)
 */

?>
