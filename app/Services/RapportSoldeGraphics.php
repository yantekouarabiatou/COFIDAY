<?php

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * Éléments graphiques des exports du rapport des soldes :
 *  - logo COFIMA redimensionné (l'original fait 6537 px de large)
 *  - diagramme en bâtonnets verticaux généré en PNG (GD), pour le PDF
 */
class RapportSoldeGraphics
{
    private const ROUGE = [220, 53, 69];
    private const VERT  = [47, 158, 68];

    /**
     * Chemin d'un PNG du logo COFIMA en largeur réduite (mis en cache dans storage/app).
     */
    public static function logoPath(int $largeur = 700): ?string
    {
        $cache = storage_path("app/rapport_logo_cofima_{$largeur}.png");
        if (is_file($cache)) {
            return $cache;
        }

        $source = collect([
            public_path('assets/img/logo_cofima_bon.jpg'),
            storage_path('app/public/photos/logo-cofima-bon.jpg'),
            storage_path('app/public/company/logo_cofima_bon.jpg'),
        ])->first(fn ($p) => is_file($p));

        if (!$source || !function_exists('imagecreatefromjpeg')) {
            return $source;
        }

        $img = @imagecreatefromjpeg($source);
        if (!$img) {
            return $source;
        }

        $hauteur = (int) round(imagesy($img) * $largeur / imagesx($img));
        $petit   = imagescale($img, $largeur, $hauteur, IMG_BICUBIC);
        imagepng($petit, $cache, 9);
        imagedestroy($img);
        imagedestroy($petit);

        return $cache;
    }

    public static function logoBase64(): ?string
    {
        $path = self::logoPath();
        if (!$path) {
            return null;
        }
        $mime = str_ends_with(strtolower($path), '.png') ? 'image/png' : 'image/jpeg';

        return "data:{$mime};base64," . base64_encode(file_get_contents($path));
    }

    /**
     * Diagramme en bâtonnets verticaux empilés (consommés en rouge, restants en vert).
     *
     * @param Collection $lignes éléments ['label' => string, 'pris' => float, 'restants' => float]
     * @return array{src: string, width: int, height: int}|null
     */
    public static function diagramme(Collection $lignes): ?array
    {
        if ($lignes->isEmpty() || !function_exists('imagettftext')) {
            return null;
        }

        $font     = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf');
        $fontBold = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf');
        if (!is_file($font)) {
            return null;
        }
        $fontBold = is_file($fontBold) ? $fontBold : $font;

        $n = $lignes->count();

        // Libellés tronqués + leur projection réelle à 45° (mesurée avec la police)
        $lignes = $lignes->values()->map(function ($l) use ($font) {
            $l['label'] = mb_strlen($l['label']) > 26 ? mb_substr($l['label'], 0, 25) . '…' : $l['label'];
            $box        = imagettfbbox(10, 0, $font, $l['label']);
            $l['proj']  = (int) ceil(($box[2] - $box[0]) * M_SQRT1_2);
            return $l;
        });
        $projMax  = $lignes->max('proj');
        $margeBas = 30 + $projMax;
        $margeH   = 60;
        $plotH    = 460;
        $pas      = max(34, min(90, (int) floor(1500 / $n)));
        // le 1er libellé incliné part vers la gauche : la marge doit le contenir
        $margeG   = max(70, $projMax - (int) ($pas / 2) + 10);
        $w        = $margeG + $n * $pas + 30;
        $h        = $margeH + $plotH + $margeBas;

        $im    = imagecreatetruecolor($w, $h);
        $blanc = imagecolorallocate($im, 255, 255, 255);
        $noir  = imagecolorallocate($im, 50, 50, 50);
        $gris  = imagecolorallocate($im, 225, 225, 225);
        $rouge = imagecolorallocate($im, ...self::ROUGE);
        $vert  = imagecolorallocate($im, ...self::VERT);
        imagefill($im, 0, 0, $blanc);

        // Échelle
        $max  = max(1, $lignes->max(fn ($l) => $l['pris'] + max($l['restants'], 0)));
        $tick = self::pasGraduation($max);
        $top  = ceil($max * 1.08 / $tick) * $tick;   // marge pour les valeurs au-dessus des barres
        $y0   = $margeH + $plotH;
        $toY  = fn ($v) => $y0 - $v / $top * $plotH;

        // Grille + axe Y
        for ($v = 0; $v <= $top + 0.001; $v += $tick) {
            $y = (int) round($toY($v));
            imageline($im, $margeG, $y, $w - 20, $y, $gris);
            $txt = rtrim(rtrim(number_format($v, 1, ',', ''), '0'), ',');
            $box = imagettfbbox(10, 0, $font, $txt);
            imagettftext($im, 10, 0, $margeG - 8 - ($box[2] - $box[0]), $y + 5, $noir, $font, $txt);
        }
        imageline($im, $margeG, $margeH, $margeG, $y0, $noir);
        imageline($im, $margeG, $y0, $w - 20, $y0, $noir);
        imagettftext($im, 10, 90, $margeG - 42, $margeH + (int) ($plotH / 2) + 15, $noir, $font, 'Jours');

        // Légende
        imagefilledrectangle($im, $margeG, 18, $margeG + 16, 34, $rouge);
        imagettftext($im, 11, 0, $margeG + 22, 32, $noir, $font, 'Jours consommés');
        imagefilledrectangle($im, $margeG + 170, 18, $margeG + 186, 34, $vert);
        imagettftext($im, 11, 0, $margeG + 192, 32, $noir, $font, 'Jours restants');

        // Barres
        $largeurBarre = (int) round($pas * 0.6);
        foreach ($lignes->values() as $i => $l) {
            $x1    = $margeG + $i * $pas + (int) (($pas - $largeurBarre) / 2);
            $x2    = $x1 + $largeurBarre;
            $pris  = max($l['pris'], 0);
            $rest  = max($l['restants'], 0);
            $yPris = (int) round($toY($pris));
            $yTot  = (int) round($toY($pris + $rest));

            if ($pris > 0) {
                imagefilledrectangle($im, $x1, $yPris, $x2, $y0 - 1, $rouge);
            }
            if ($rest > 0) {
                imagefilledrectangle($im, $x1, $yTot, $x2, $yPris, $vert);
            }

            // Valeur au-dessus : consommés / restants
            $val = self::fmt($l['pris']) . '/' . self::fmt($l['restants']);
            $box = imagettfbbox(9, 0, $fontBold, $val);
            $tw  = $box[2] - $box[0];
            imagettftext($im, 9, 0, (int) ($x1 + ($largeurBarre - $tw) / 2), $yTot - 6, $noir, $fontBold, $val);

            // Libellé incliné à 45° sous l'axe
            // le texte monte vers la droite et se termine sous le centre de la barre
            $d = $l['proj'];
            imagettftext($im, 10, 45, (int) ($x1 + $largeurBarre / 2 - $d + 4), $y0 + 12 + $d, $noir, $font, $l['label']);
        }

        ob_start();
        imagepng($im);
        $png = ob_get_clean();
        imagedestroy($im);

        return ['src' => 'data:image/png;base64,' . base64_encode($png), 'width' => $w, 'height' => $h];
    }

    private static function pasGraduation(float $max): float
    {
        foreach ([1, 2, 5, 10, 20, 25, 50, 100, 200, 250, 500, 1000] as $p) {
            if ($max / $p <= 8) {
                return $p;
            }
        }

        return 2000;
    }

    private static function fmt(float $v): string
    {
        return rtrim(rtrim(number_format($v, 1, ',', ''), '0'), ',');
    }
}
