<?php

// gamification.php
// Logic for Ranks and Badges based on user stats.

/**
 * Returns the Rank definition based on reputation points.
 */
function getRank($reputation) {
    $ranks = [
        ['min' => 0, 'max' => 49, 'name' => 'Látogató', 'color' => '#A0A0A0', 'icon' => 'fa-user', 'style' => 'text-gray-400'],
        ['min' => 50, 'max' => 199, 'name' => 'Újonc', 'color' => '#FFFFFF', 'icon' => 'fa-seedling', 'style' => 'text-white'],
        ['min' => 200, 'max' => 499, 'name' => 'Felfedező', 'color' => '#00D4FF', 'icon' => 'fa-compass', 'style' => 'text-cyan-400 drop-shadow-[0_0_5px_rgba(34,211,238,0.8)]'],
        ['min' => 500, 'max' => 1499, 'name' => 'Aktív Tag', 'color' => '#00FF9D', 'icon' => 'fa-check-circle', 'style' => 'text-emerald-400 drop-shadow-[0_0_5px_rgba(52,211,153,0.8)]'],
        ['min' => 1500, 'max' => 4999, 'name' => 'Veterán', 'color' => '#D400FF', 'icon' => 'fa-medal', 'style' => 'text-purple-400 drop-shadow-[0_0_8px_rgba(168,85,247,0.8)]'],
        ['min' => 5000, 'max' => 9999, 'name' => 'Mentor', 'color' => '#FF0055', 'icon' => 'fa-chalkboard-teacher', 'style' => 'text-pink-500 drop-shadow-[0_0_10px_rgba(236,72,153,0.8)]'],
        ['min' => 10000, 'max' => 24999, 'name' => 'Mester', 'color' => '#FFD700', 'icon' => 'fa-crown', 'style' => 'text-yellow-400 drop-shadow-[0_0_12px_rgba(250,204,21,0.9)]'],
        ['min' => 25000, 'max' => 9999999, 'name' => 'Cyber Legenda', 'color' => 'linear-gradient(45deg, #ff0000, #ff7f00, #ffff00, #00ff00, #0000ff, #4b0082, #9400d3)', 'icon' => 'fa-robot', 'style' => 'text-transparent bg-clip-text bg-gradient-to-r from-red-500 via-green-500 to-blue-500 animate-pulse font-extrabold drop-shadow-[0_0_15px_rgba(255,255,255,0.8)]']
    ];

    foreach ($ranks as $rank) {
        if ($reputation >= $rank['min'] && $reputation <= $rank['max']) {
            return $rank;
        }
    }
    return $ranks[0]; // Default fallback
}

/**
 * Returns all defined badges with their criteria.
 */
function getBadgeDefinitions() {
    return [
        // Questions (Curiosity)
        'questions' => [
            ['threshold' => 5, 'name' => 'Kíváncsi', 'icon' => 'fa-question', 'color' => 'text-blue-300', 'bg' => 'bg-blue-500/10', 'border' => 'border-blue-500/20'],
            ['threshold' => 25, 'name' => 'Kutató', 'icon' => 'fa-search', 'color' => 'text-blue-400', 'bg' => 'bg-blue-500/20', 'border' => 'border-blue-500/30'],
            ['threshold' => 75, 'name' => 'Tudásszomjas', 'icon' => 'fa-brain', 'color' => 'text-indigo-400', 'bg' => 'bg-indigo-500/20', 'border' => 'border-indigo-500/30'],
            ['threshold' => 200, 'name' => 'Nyomozó', 'icon' => 'fa-user-secret', 'color' => 'text-purple-400', 'bg' => 'bg-purple-500/20', 'border' => 'border-purple-500/30'],
            ['threshold' => 500, 'name' => 'Az Örök Kétkedő', 'icon' => 'fa-infinity', 'color' => 'text-fuchsia-400 neon-text', 'bg' => 'bg-fuchsia-900/40', 'border' => 'border-fuchsia-500/50'],
        ],
        // Answers (Helpfulness)
        'answers' => [
            ['threshold' => 5, 'name' => 'Közreműködő', 'icon' => 'fa-hands-helping', 'color' => 'text-emerald-300', 'bg' => 'bg-emerald-500/10', 'border' => 'border-emerald-500/20'],
            ['threshold' => 50, 'name' => 'Segítőtárs', 'icon' => 'fa-hand-holding-heart', 'color' => 'text-emerald-400', 'bg' => 'bg-emerald-500/20', 'border' => 'border-emerald-500/30'],
            ['threshold' => 150, 'name' => 'Tanácsadó', 'icon' => 'fa-user-graduate', 'color' => 'text-teal-400', 'bg' => 'bg-teal-500/20', 'border' => 'border-teal-500/30'],
            ['threshold' => 400, 'name' => 'Professzor', 'icon' => 'fa-glasses', 'color' => 'text-orange-400', 'bg' => 'bg-orange-500/20', 'border' => 'border-orange-500/30'],
            ['threshold' => 1000, 'name' => 'Lexikon', 'icon' => 'fa-book-open', 'color' => 'text-red-500 neon-text', 'bg' => 'bg-red-900/40', 'border' => 'border-red-500/50'],
        ],
        // Solutions (Value)
        'accepted_answers' => [
            ['threshold' => 1, 'name' => 'Hasznos', 'icon' => 'fa-thumbs-up', 'color' => 'text-amber-700', 'bg' => 'bg-amber-900/20', 'border' => 'border-amber-700/30'], // Bronze-ish
            ['threshold' => 10, 'name' => 'Problem Solver', 'icon' => 'fa-wrench', 'color' => 'text-slate-300', 'bg' => 'bg-slate-500/20', 'border' => 'border-slate-400/30'], // Silver-ish
            ['threshold' => 50, 'name' => 'Hibaelhárító', 'icon' => 'fa-check-double', 'color' => 'text-yellow-400', 'bg' => 'bg-yellow-500/20', 'border' => 'border-yellow-500/30'], // Gold
            ['threshold' => 150, 'name' => 'Mágus', 'icon' => 'fa-magic', 'color' => 'text-cyan-400', 'bg' => 'bg-cyan-500/20', 'border' => 'border-cyan-500/30'], // Diamond Blue
            ['threshold' => 500, 'name' => 'Orákulum', 'icon' => 'fa-eye', 'color' => 'text-amber-300 neon-text', 'bg' => 'bg-black border-2', 'border' => 'border-amber-500'], // Black/Gold
        ],
        // Comments (Conversation)
        'comments' => [
            ['threshold' => 10, 'name' => 'Megszólaló', 'icon' => 'fa-comment', 'color' => 'text-slate-400', 'bg' => 'bg-slate-500/10', 'border' => 'border-slate-500/20'],
            ['threshold' => 100, 'name' => 'Csevegő', 'icon' => 'fa-comments', 'color' => 'text-yellow-200', 'bg' => 'bg-yellow-500/10', 'border' => 'border-yellow-500/20'],
            ['threshold' => 500, 'name' => 'Vitapartner', 'icon' => 'fa-bullhorn', 'color' => 'text-orange-400', 'bg' => 'bg-orange-500/20', 'border' => 'border-orange-500/30'],
            ['threshold' => 2000, 'name' => 'Szószóló', 'icon' => 'fa-microphone', 'color' => 'text-red-400', 'bg' => 'bg-red-500/20', 'border' => 'border-red-500/30'],
            ['threshold' => 5000, 'name' => 'Billentyűhuszár', 'icon' => 'fa-keyboard', 'color' => 'text-pink-500 neon-text', 'bg' => 'bg-pink-900/40', 'border' => 'border-pink-500/50'],
        ],
        // Reactions (Popularity)
        'reactions' => [
            ['threshold' => 50, 'name' => 'Észrevettek', 'icon' => 'fa-heart', 'color' => 'text-rose-300', 'bg' => 'bg-rose-500/10', 'border' => 'border-rose-500/20'],
            ['threshold' => 250, 'name' => 'Kedvelt', 'icon' => 'fa-grin-hearts', 'color' => 'text-rose-400', 'bg' => 'bg-rose-500/20', 'border' => 'border-rose-500/30'],
            ['threshold' => 1000, 'name' => 'Népszerű', 'icon' => 'fa-star', 'color' => 'text-fuchsia-400', 'bg' => 'bg-fuchsia-500/20', 'border' => 'border-fuchsia-500/30'],
            ['threshold' => 5000, 'name' => 'Celeb', 'icon' => 'fa-camera', 'color' => 'text-purple-500', 'bg' => 'bg-purple-600/20', 'border' => 'border-purple-600/30'],
            ['threshold' => 15000, 'name' => 'Influencer', 'icon' => 'fa-fire', 'color' => 'text-yellow-400 neon-text', 'bg' => 'bg-yellow-600/30', 'border' => 'border-yellow-500/50'],
        ]
    ];
}

/**
 * Calculates badges based on user stats.
 * Returns array of badges that should be awarded.
 */
function calculateEarnedBadges($stats) {
    $earned = [];
    $definitions = getBadgeDefinitions();

    // Map stats keys to definition keys
    $mapping = [
        'questions' => 'questions_count',
        'answers' => 'answers_count',
        'accepted_answers' => 'accepted_answers_count',
        'comments' => 'comments_count',
        'reactions' => 'reactions_count'
    ];

    foreach ($definitions as $category => $badges) {
        $statKey = $mapping[$category] ?? null;
        if (!$statKey) continue;

        $userValue = $stats[$statKey] ?? 0;

        foreach ($badges as $badge) {
            if ($userValue >= $badge['threshold']) {
                $earned[] = $badge;
            }
        }
    }
    return $earned;
}

/**
 * Syncs user rank and badges to the database.
 * This should be called when viewing the profile to ensure everything is up to date.
 */
function syncUserGamification($pdo, $userId, $userStats) {
    if (!$userId || !$pdo) return;

    // 1. Update Rank
    $reputation = $userStats['reputation_points'] ?? 0;
    $rank = getRank($reputation);

    // Only update if changed (optimization)
    if (($userStats['rank_title'] ?? '') !== $rank['name']) {
        $stmt = $pdo->prepare("UPDATE qc_users SET rank_title = ? WHERE user_id = ?");
        $stmt->execute([$rank['name'], $userId]);
    }

    // 2. Sync Badges
    // First, get all badges from DB to map Names to IDs (since we use names in code)
    // We fetch all badges to avoid multiple queries
    $stmt = $pdo->query("SELECT * FROM qc_badges");
    $allDbBadges = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $badgeMap = [];
    foreach ($allDbBadges as $b) {
        $badgeMap[$b['name']] = $b['id'];
    }

    // Calculate what user SHOULD have
    $earnedBadges = calculateEarnedBadges($userStats);

    // If no badges earned, return early
    if (empty($earnedBadges)) return;

    // Get what user ALREADY has
    $stmt = $pdo->prepare("SELECT badge_id FROM qc_user_badges WHERE user_id = ?");
    $stmt->execute([$userId]);
    $existingBadgeIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($earnedBadges as $earned) {
        $badgeName = $earned['name'];
        $badgeId = null;

        // Check if badge exists in DB mapping
        if (isset($badgeMap[$badgeName])) {
            $badgeId = $badgeMap[$badgeName];
        } else {
            // Auto-seed missing badge definition if not found
            // This ensures DB is always in sync with code definitions
            $stmt = $pdo->prepare("INSERT INTO qc_badges (name, description, icon) VALUES (?, ?, ?)");
            $desc = "Awarded for " . $earned['threshold'] . "+ activity."; // Simple description
            $stmt->execute([$badgeName, $desc, $earned['icon']]);
            $badgeId = $pdo->lastInsertId();
            $badgeMap[$badgeName] = $badgeId; // Update local map
        }

        // Assign to user if not already assigned
        if ($badgeId && !in_array($badgeId, $existingBadgeIds)) {
            $stmt = $pdo->prepare("INSERT INTO qc_user_badges (user_id, badge_id) VALUES (?, ?)");
            $stmt->execute([$userId, $badgeId]);
            $existingBadgeIds[] = $badgeId; // Add to local list to prevent duplicates in this loop
        }
    }
}
?>