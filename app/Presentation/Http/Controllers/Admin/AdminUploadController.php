<?php

declare(strict_types=1);
/**
 * Hyperf API — DDD / Hexagonal
 *
 * @link     https://github.com/VictordaSilvaf/hyperf_port
 * @document https://github.com/VictordaSilvaf/hyperf_port/doc
 * @contact  victordasilvafernandes@gmail.com
 * @see      https://github.com/VictordaSilvaf/hyperf_port.git
 */

namespace App\Presentation\Http\Controllers\Admin;

use App\Application\Upload\StoreUpload\StoreUploadCommand;
use App\Application\Upload\StoreUpload\StoreUploadHandler;
use App\Presentation\Http\Controllers\AbstractController;
use App\Presentation\Http\OpenApi\OpenApiRefs;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Swagger\Annotation as SA;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Throwable;

use function Hyperf\Translation\trans;

#[SA\HyperfServer('openapi')]
final class AdminUploadController extends AbstractController
{
    #[Inject]
    protected StoreUploadHandler $storeUpload;

    #[SA\Post(path: '/api/v1/admin/uploads', summary: 'Upload de ficheiro', description: 'Requires permission: uploads.create', security: OpenApiRefs::BEARER, tags: ['Admin Uploads'])]
    #[SA\RequestBody(content: [
        new SA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new SA\Schema(
                required: ['file'],
                properties: [new SA\Property(property: 'file', type: 'string', format: 'binary')],
            ),
        ),
    ])]
    #[SA\Response(response: 200, description: 'Upload OK', content: new SA\JsonContent(ref: '#/components/schemas/UploadResult'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
    public function store(): array|PsrResponseInterface
    {
        $file = $this->request->file('file');
        if ($file === null) {
            return $this->response->json(['message' => trans('http.validation_failed')])->withStatus(422);
        }

        if (is_object($file) && method_exists($file, 'isValid') && ! $file->isValid()) {
            $error = method_exists($file, 'getError') ? (int) $file->getError() : 0;

            return $this->response->json([
                'message' => 'Upload failed (PHP error code ' . $error . '). Check file size limits.',
            ])->withStatus(422);
        }

        $contents = $this->readUploadedContents($file);
        if ($contents === '') {
            return $this->response->json([
                'message' => 'Uploaded file is empty or could not be read.',
            ])->withStatus(422);
        }

        try {
            return $this->storeUpload->handle(new StoreUploadCommand(
                $contents,
                (string) $file->getClientFilename(),
                (string) ($file->getClientMediaType() ?? 'application/octet-stream'),
            ));
        } catch (Throwable $exception) {
            return $this->response->json([
                'message' => $exception->getMessage() !== ''
                    ? $exception->getMessage()
                    : trans('http.internal_server_error'),
            ])->withStatus(500);
        }
    }

    private function readUploadedContents(mixed $file): string
    {
        // Prefer temp pathname — more reliable on Swoole than stream getContents().
        if (is_object($file) && method_exists($file, 'getPathname')) {
            $pathname = (string) $file->getPathname();
            if ($pathname !== '' && is_readable($pathname)) {
                $contents = file_get_contents($pathname);

                return $contents === false ? '' : $contents;
            }
        }

        if (is_object($file) && method_exists($file, 'getStream')) {
            $stream = $file->getStream();
            if (is_object($stream) && method_exists($stream, 'rewind')) {
                try {
                    $stream->rewind();
                } catch (Throwable) {
                }
            }
            if (is_object($stream) && method_exists($stream, 'getContents')) {
                return (string) $stream->getContents();
            }
        }

        return '';
    }
}
