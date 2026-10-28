<?php

/**
 * 近一年文章更新热力数据
 */
function sleek_heatmap_data()
{
    static $data = null;

    if ($data !== null) {
        return $data;
    }

    $posts = sleek_all_posts();
    $today = new DateTime('today');
    $end = (clone $today)->modify('sunday this week');
    $start = (clone $end)->modify('-181 days');
    $dayCount = (int) $start->diff($end)->days + 1;
    $startTs = $start->getTimestamp();
    $todayEndTs = (clone $today)->setTime(23, 59, 59)->getTimestamp();
    $todayKey = $today->format('Y-m-d');

    $counts = array();
    $titles = array();

    foreach ($posts as $post) {
        $created = (int) $post['created'];

        if ($created < $startTs || $created > $todayEndTs) {
            continue;
        }

        $key = date('Y-m-d', $created);
        $counts[$key] = isset($counts[$key]) ? $counts[$key] + 1 : 1;

        if (!isset($titles[$key])) {
            $titles[$key] = array();
        }

        if (count($titles[$key]) < 3) {
            $titles[$key][] = (string) $post['title'];
        }
    }

    $total = 0;
    $activeDays = 0;
    $longestStreak = 0;
    $run = 0;
    $cells = array();

    foreach ($counts as $date => $count) {
        $total += (int) $count;

        if ($count > 0) {
            $activeDays++;
        }
    }

    $cursor = clone $start;

    for ($i = 0; $i < $dayCount; $i++) {
        $date = $cursor->format('Y-m-d');
        $count = isset($counts[$date]) ? (int) $counts[$date] : 0;
        $future = $cursor > $today;
        $level = 0;

        if ($count >= 5) {
            $level = 4;
        } elseif ($count >= 3) {
            $level = 3;
        } elseif ($count >= 2) {
            $level = 2;
        } elseif ($count >= 1) {
            $level = 1;
        }

        if ($count > 0) {
            $run++;
            $longestStreak = max($longestStreak, $run);
        } else {
            $run = 0;
        }

        $label = $date;

        if ($future) {
            $label .= ' · 尚未到来';
        } elseif ($count > 0) {
            $label .= ' · ' . $count . ' 篇';

            if (!empty($titles[$date])) {
                $label .= '：' . implode('、', $titles[$date]);
            }
        } else {
            $label .= ' · 未更新';
        }

        $cells[] = array(
            'date' => $date,
            'count' => $count,
            'level' => $level,
            'future' => $future,
            'today' => $date === $todayKey,
            'label' => $label,
        );

        $cursor->modify('+1 day');
    }

    $currentStreak = 0;
    $streakCursor = clone $today;

    if (empty($counts[$todayKey])) {
        $streakCursor->modify('-1 day');
    }

    while (true) {
        $streakKey = $streakCursor->format('Y-m-d');

        if (empty($counts[$streakKey])) {
            break;
        }

        $currentStreak++;
        $streakCursor->modify('-1 day');
    }

    $data = array(
        'cells' => $cells,
        'total' => $total,
        'activeDays' => $activeDays,
        'currentStreak' => $currentStreak,
        'longestStreak' => $longestStreak,
        'start' => $start,
        'today' => $today,
    );

    return $data;
}
