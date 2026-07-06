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

namespace App\Infrastructure\Page;

use App\Application\Page\BlockRegistryInterface;
use App\Domain\Page\Block\BlockValidatorInterface;
use App\Domain\Page\Block\ContactFormBlockValidator;
use App\Domain\Page\Block\CtaBlockValidator;
use App\Domain\Page\Block\EmbedBlockValidator;
use App\Domain\Page\Block\FeaturedProjectsBlockValidator;
use App\Domain\Page\Block\GalleryBlockValidator;
use App\Domain\Page\Block\HeroBlockValidator;
use App\Domain\Page\Block\ImageBlockValidator;
use App\Domain\Page\Block\MarkdownBlockValidator;
use App\Domain\Page\Block\ProjectListBlockValidator;
use App\Domain\Page\Block\SpacerBlockValidator;
use App\Domain\Page\Block\TechStackBlockValidator;
use App\Domain\Page\Exception\UnknownBlockTypeException;

final class BlockRegistry implements BlockRegistryInterface
{
    /** @var array<string, BlockValidatorInterface> */
    private array $validators;

    public function __construct()
    {
        $this->validators = [];
        foreach ($this->createValidators() as $validator) {
            $this->validators[$validator->type()] = $validator;
        }
    }

    public function get(string $type): BlockValidatorInterface
    {
        if (! isset($this->validators[$type])) {
            throw UnknownBlockTypeException::forType($type);
        }

        return $this->validators[$type];
    }

    public function all(): array
    {
        return array_values($this->validators);
    }

    public function metadata(): array
    {
        return array_map(
            static fn (BlockValidatorInterface $validator): array => [
                'type' => $validator->type(),
                'label' => $validator->label(),
                'schema' => $validator->schema(),
            ],
            $this->all()
        );
    }

    public function validate(string $type, array $payload): void
    {
        $this->get($type)->validate($payload);
    }

    /** @return list<BlockValidatorInterface> */
    private function createValidators(): array
    {
        return [
            new HeroBlockValidator(),
            new MarkdownBlockValidator(),
            new ImageBlockValidator(),
            new GalleryBlockValidator(),
            new FeaturedProjectsBlockValidator(),
            new ProjectListBlockValidator(),
            new TechStackBlockValidator(),
            new CtaBlockValidator(),
            new ContactFormBlockValidator(),
            new EmbedBlockValidator(),
            new SpacerBlockValidator(),
        ];
    }
}
