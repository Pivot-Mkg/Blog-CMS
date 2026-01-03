<?php

/**
 * Shared helper to load/save homepage hero settings independent of blog posts.
 */

const HOME_HERO_PATH = __DIR__ . '/../database/home_hero.json';

function default_home_hero(): array
{
    return [
        'title' => 'Stories from our team',
        'subtitle' => 'Insights, releases, and lessons learned. Stay in the loop with our latest posts.',
        'cta_label' => 'Browse posts',
        'cta_url' => '/index.php',
        'image' => DEFAULT_BANNER_IMAGE_PATH,
        'image_alt' => 'Blog hero banner',
    ];
}

function home_hero_resolve_url(string $value): string
{
    if ($value === '') {
        return BASE_URL . 'index.php';
    }
    if (preg_match('#^https?://#i', $value)) {
        return $value;
    }
    if (str_starts_with($value, BASE_URL)) {
        return $value;
    }
    return BASE_URL . ltrim($value, '/');
}

function home_hero_resolve_image(string $value): string
{
    if ($value === '') {
        $value = DEFAULT_BANNER_IMAGE_PATH;
    }
    if (preg_match('#^https?://#i', $value)) {
        return $value;
    }
    if (str_starts_with($value, BASE_URL)) {
        return $value;
    }
    return BASE_URL . ltrim($value, '/');
}

function get_home_hero(): array
{
    $defaults = default_home_hero();
    if (!file_exists(HOME_HERO_PATH)) {
        return $defaults;
    }
    $json = file_get_contents(HOME_HERO_PATH);
    $data = json_decode($json, true);
    if (!is_array($data)) {
        return $defaults;
    }
    return array_merge($defaults, array_intersect_key($data, $defaults));
}

function save_home_hero(array $input): bool
{
    $cta = trim($input['cta_url'] ?? '');
    if ($cta !== '' && str_starts_with($cta, BASE_URL)) {
        $cta = '/' . ltrim(substr($cta, strlen(BASE_URL)), '/');
    }

    $image = trim($input['image'] ?? '');
    if ($image !== '' && str_starts_with($image, BASE_URL)) {
        $image = substr($image, strlen(BASE_URL));
    }

    $data = [
        'title' => trim(strip_tags($input['title'] ?? '')),
        'subtitle' => trim(strip_tags($input['subtitle'] ?? '')),
        'cta_label' => trim(strip_tags($input['cta_label'] ?? '')),
        'cta_url' => $cta,
        'image' => $image ?: DEFAULT_BANNER_IMAGE_PATH,
        'image_alt' => trim(strip_tags($input['image_alt'] ?? '')),
    ];

    if ($data['cta_url'] !== '' && !preg_match('#^https?://#i', $data['cta_url']) && !str_starts_with($data['cta_url'], '/')) {
        $data['cta_url'] = '/' . ltrim($data['cta_url'], '/');
    }
    if ($data['cta_url'] === '') {
        $data['cta_url'] = '/index.php';
    }
    if ($data['image_alt'] === '') {
        $data['image_alt'] = $data['title'] !== '' ? ($data['title'] . ' banner') : 'Blog hero banner';
    }

    $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    return (bool)file_put_contents(HOME_HERO_PATH, $encoded);
}
