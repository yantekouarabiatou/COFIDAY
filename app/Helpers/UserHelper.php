<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

/**
 * Small helper utilities for user presentation (avatar, initials, color).
 */
class UserHelper
{
    /**
     * Return avatar URL if available, otherwise null.
     * Accepts either a full URL or a stored path.
     */
    public static function getAvatarUrl($user): ?string
    {
        if (empty($user)) {
            return null;
        }

        // If the model has an 'avatar' attribute and it's a full URL, return it
        if (!empty($user->avatar) && (Str::startsWith($user->avatar, ['http://', 'https://']))) {
            return $user->avatar;
        }

        // If avatar looks like a storage path, attempt to generate an asset URL
        if (!empty($user->avatar)) {
            // If file exists in storage/app/public/avatars
            $possible = 'storage/' . ltrim($user->avatar, '/');
            return asset($possible);
        }

        return null;
    }

    /**
     * Return initials for display (e.g., JS -> J.S.)
     */
    public static function getInitials($user): string
    {
        if (empty($user)) {
            return '';
        }

        $prenom = trim((string) ($user->prenom ?? ''));
        $nom = trim((string) ($user->nom ?? ''));

        if ($prenom || $nom) {
            $parts = array_filter([$prenom, $nom]);
            $initials = '';
            foreach ($parts as $p) {
                $initials .= mb_strtoupper(mb_substr(trim($p), 0, 1));
            }
            return $initials;
        }

        // fallback on 'name' or email
        $name = trim((string) ($user->name ?? ''));
        if ($name) {
            $words = preg_split('/\s+/', $name);
            $init = '';
            foreach (array_slice($words, 0, 2) as $w) {
                $init .= mb_strtoupper(mb_substr($w, 0, 1));
            }
            return $init;
        }

        if (!empty($user->email)) {
            return mb_strtoupper(mb_substr($user->email, 0, 1));
        }

        return '';
    }

    /**
     * Deterministic background color for initials avatar based on user id or email.
     */
    public static function getAvatarColor($user): string
    {
        $palette = [
            '#004080', '#003366', '#2b6cb0', '#4c51bf', '#2f855a', '#d69e2e', '#dd6b20', '#b83280'
        ];

        $seed = $user->id ?? ($user->email ?? Str::random(8));
        $hash = crc32((string) $seed);
        $index = $hash % count($palette);
        return $palette[$index];
    }

    public static function hoursToHoursMinutes(float $decimal): string
    {
        $hours = floor($decimal);
        $minutes = round(($decimal - $hours) * 60);
        return "{$hours}h {$minutes}min";
    }
}
