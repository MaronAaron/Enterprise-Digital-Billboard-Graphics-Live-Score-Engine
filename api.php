<?php
/**
 * Live Football Score Billboard Graphics System - API Controller
 * Clean, production-ready backend engine for tracking real-time live events.
 */

// ==========================================
// 1. CONFIGURATION & STATE INITIALIZATION
// ==========================================
$apiKey   = "46600d2e77310afc0dd075f2b04549389527b985f5abee04aef06ad6d102e1ca";
$apiUrl   = "https://apiv3.apifootball.com/?action=get_livescores&APIkey=" . $apiKey;
$dataFile = __DIR__ . '/livescores.json';

// Configuration Flags
$DEMO_MODE = true; // Set to FALSE for production live api operations

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Cache-Control: no-cache, must-revalidate");

// ==========================================
// 2. STABLE DEMO SIMULATION CONTROLLER
// ==========================================
if ($DEMO_MODE) {
    $stepFile = __DIR__ . '/demo_step.txt';
    $currentTicks = file_exists($stepFile) ? (int)file_get_contents($stepFile) : 0;
    $currentTicks++;
    file_put_contents($stepFile, $currentTicks);

    // Each scenario runs for 15 server ticks (15 ticks * 2s poll = 30 seconds display persistence)
    if ($currentTicks <= 15) {
        $scenario = 1;
    } elseif ($currentTicks <= 30) {
        $scenario = 2;
    } elseif ($currentTicks <= 45) {
        $scenario = 3;
    } else {
        $scenario = 1;
        file_put_contents($stepFile, 0); // Reset the state ticks loop
    }

    $demoPayload = null;

    // SCENARIO 1: Regional Match - Side Panel Popout Overlay
    if ($scenario === 1) {
        $demoPayload = [
            'type'           => 'goal',
            'home'           => 'Vipers SC',
            'away'           => 'KCCA FC',
            'h_score'        => 1,
            'a_score'        => 0,
            'home_logo'      => 'https://apiv3.apifootball.com/badges/logo_match/14175_vipers.png', 
            'away_logo'      => 'https://apiv3.apifootball.com/badges/logo_match/14172_kcca.png',
            'league'         => 'Uganda Premier League',
            'scorer'         => 'Milton Karisa',
            'minute'         => '12\'',
            'is_high_stakes' => false,
            'team_color'     => '#E32221'
        ];
    } 
    // SCENARIO 2: High Stakes Continental Clash - Takeover Full Screen Interrupt
    elseif ($scenario === 2) {
        $demoPayload = [
            'type'           => 'goal',
            'home'           => 'Real Madrid',
            'away'           => 'Man City',
            'h_score'        => 2,
            'a_score'        => 1,
            'home_logo'      => 'https://apiv3.apifootball.com/badges/76_real-madrid.jpg', 
            'away_logo'      => 'https://apiv3.apifootball.com/badges/80_manchester-city.jpg',
            'league'         => 'UEFA Champions League - Final',
            'scorer'         => 'Vinícius Júnior',
            'minute'         => '89\'',
            'is_high_stakes' => true,
            'team_color'     => '#00143C'
        ];
    } 
    // SCENARIO 3: VAR Disallowed Goal Interruption
    elseif ($scenario === 3) {
        $demoPayload = [
            'type'           => 'var',
            'home'           => 'Uganda',
            'away'           => 'Senegal',
            'h_score'        => 0,
            'a_score'        => 0,
            'home_logo'      => 'https://apiv3.apifootball.com/badges/logo_country/114_uganda.png', 
            'away_logo'      => 'https://apiv3.apifootball.com/badges/logo_country/101_senegal.png',
            'league'         => 'Africa Cup of Nations (AFCON)',
            'scorer'         => 'VAR DECISION',
            'minute'         => 'GOAL DISALLOWED - OFFSIDE',
            'is_high_stakes' => true,
            'team_color'     => '#FF0000'
        ];
    }

    echo json_encode($demoPayload);
    exit;
}

// ==========================================
// 3. REAL-TIME API PARSING PRODUCTION LOGIC
// ==========================================
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
curl_close($ch);

$currentMatches = json_decode($response, true);
$previousData   = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
$payload        = null;

$bigLeagues = ['Champions League', 'Europa League', 'Premier League', 'La Liga', 'Africa Cup of Nations', 'Uganda Premier League'];
$bigStages  = ['Final', 'Semi-final', 'Quarter-final'];

if (is_array($currentMatches) && !isset($currentMatches['error'])) {
    $currentScoresToStore = [];

    foreach ($currentMatches as $match) {
        $matchId    = $match['match_id'];
        $homeScore  = (int)$match['match_hometeam_score'];
        $awayScore  = (int)$match['match_awayteam_score'];
        $totalGoals = $homeScore + $awayScore;

        $currentScoresToStore[$matchId] = $totalGoals;

        // Smart Logic Flag Evaluation
        $isHighStakes = false;
        if (in_array($match['league_name'], $bigLeagues) || in_array($match['stage_name'], $bigStages)) {
            $isHighStakes = true;
        }

        // Resolving dynamic themes natively based on League Name metadata matching
        $themeColor = '#FFCC00'; 
        $leagueLower = strtolower($match['league_name']);
        if (strpos($leagueLower, 'premier league') !== false) $themeColor = '#3d195d';
        if (strpos($leagueLower, 'la liga') !== false)        $themeColor = '#ee3124';
        if (strpos($leagueLower, 'champions league') !== false) $themeColor = '#003399';
        if (strpos($leagueLower, 'uganda') !== false)         $themeColor = '#E32221';

        // Extract latest goalscorer cleanly
        $scorerName = "Goal Alert!";
        $minute     = $match['match_time'] . "'";
        if (!empty($match['goalscorer']) && is_array($match['goalscorer'])) {
            $lastGoal   = end($match['goalscorer']);
            $scorerName = !empty($lastGoal['home_scorer']) ? $lastGoal['home_scorer'] : $lastGoal['away_scorer'];
            $minute     = $lastGoal['time'] . "'";
        }

        // Detect State Changes against standard memory file cache
        if (isset($previousData[$matchId])) {
            if ($totalGoals > $previousData[$matchId]) {
                // GOAL ENGINE EMISSION
                $payload = [
                    'type'           => 'goal',
                    'home'           => $match['match_hometeam_name'],
                    'away'           => $match['match_awayteam_name'],
                    'h_score'        => $homeScore,
                    'a_score'        => $awayScore,
                    'home_logo'      => $match['team_home_badge'],
                    'away_logo'      => $match['team_away_badge'],
                    'league'         => $match['league_name'],
                    'scorer'         => $scorerName,
                    'minute'         => $minute,
                    'is_high_stakes' => $isHighStakes,
                    'team_color'     => $themeColor
                ];
                break;
            } 
            elseif ($totalGoals < $previousData[$matchId]) {
                // ACTIVE REF REVERSAL / VAR EVENT EMISSION
                $payload = [
                    'type'           => 'var',
                    'home'           => $match['match_hometeam_name'],
                    'away'           => $match['match_awayteam_name'],
                    'h_score'        => $homeScore,
                    'a_score'        => $awayScore,
                    'home_logo'      => $match['team_home_badge'],
                    'away_logo'      => $match['team_away_badge'],
                    'league'         => $match['league_name'],
                    'scorer'         => 'GOAL CANCELLED',
                    'minute'         => 'VAR OVERTURN',
                    'is_high_stakes' => true,
                    'team_color'     => '#FF0000'
                ];
                break;
            }
        }
    }
    file_put_contents($dataFile, json_encode($currentScoresToStore));
}

echo json_encode($payload);
exit;
