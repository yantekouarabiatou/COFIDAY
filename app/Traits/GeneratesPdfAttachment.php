<?php

namespace App\Traits;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Log;

trait GeneratesPdfAttachment
{
    /**
     * Retourne le logo COFIMA encodé en base64 (compatible Linux + Windows).
     */
    protected function getLogoBase64(): ?string
    {
        $path = storage_path('app/public/photos/logo-cofima-bon.jpg');
        if (! file_exists($path)) {
            return null;
        }
        return 'data:image/jpeg;base64,' . base64_encode(file_get_contents($path));
    }

    /**
     * Génère un Attachment PDF à partir d'une vue Blade.
     *
     * @param  string  $view      Chemin de la vue (ex: 'pdfs.refus_conge')
     * @param  array   $data      Variables passées à la vue
     * @param  string  $filename  Nom du fichier PDF
     * @param  string  $context   Label pour les logs d'erreur
     * @return Attachment|null
     */
    protected function buildPdfAttachment(string $view, array $data, string $filename, string $context = ''): ?Attachment
    {
        try {
            $pdf = Pdf::loadView($view, array_merge($data, [
                'logoBase64' => $this->getLogoBase64(),
            ]));

            return Attachment::fromData(
                fn () => $pdf->output(),
                $filename
            )->withMime('application/pdf');

        } catch (\Throwable $e) {
            Log::error("GeneratesPdfAttachment [{$context}] échec génération PDF", [
                'view'  => $view,
                'error' => $e->getMessage(),
                'file'  => $e->getFile() . ':' . $e->getLine(),
            ]);
            return null;
        }
    }

    /**
     * Retourne un tableau d'Attachments en filtrant les nulls.
     */
    protected function buildAttachments(array $items): array
    {
        return array_values(array_filter($items));
    }
}
