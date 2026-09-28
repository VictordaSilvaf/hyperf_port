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
use Hyperf\Database\Migrations\Migration;
use Hyperf\DbConnection\Db;

/*
 * Seed inicial do portfólio público (Tgarante, Fui/AppFui, Cage).
 * Idempotente: insertOrIgnore por id; não sobrescreve projetos já existentes.
 */
return new class extends Migration {
    private const PROJECT_TGARANTE = 'p1000001-0000-4000-8000-000000000001';

    private const PROJECT_FUI = 'p1000002-0000-4000-8000-000000000001';

    private const PROJECT_CAGE = 'p1000003-0000-4000-8000-000000000001';

    /** Seed taxonomy ids from 2026_07_04_000003_seed_default_taxonomies. */
    private const CATEGORY_WEB = 'c1000001-0000-4000-8000-000000000001';

    private const CATEGORY_MOBILE = 'c1000002-0000-4000-8000-000000000001';

    private const TECH_LARAVEL = 't1000001-0000-4000-8000-000000000001';

    private const TECH_REACT = 't1000002-0000-4000-8000-000000000001';

    private const TECH_HYPERF = 't1000003-0000-4000-8000-000000000001';

    private const TECH_POSTGRES = 't1000004-0000-4000-8000-000000000001';

    private const TAG_PORTFOLIO = 'g1000002-0000-4000-8000-000000000001';

    private const USER_ADMIN = 'c0000001-0000-4000-8000-000000000001';

    public function up(): void
    {
        $now = date('Y-m-d H:i:s');
        $ownerId = Db::table('users')->where('id', self::USER_ADMIN)->exists()
            ? self::USER_ADMIN
            : null;

        $projects = [
            [
                'id' => self::PROJECT_TGARANTE,
                'title' => 'Tgarante',
                'slug' => 'tgarante',
                'description' => 'Plataforma de imóveis, voltada para anúncio, busca e gerenciamento do mercado imobiliário.',
                'content' => <<<'MD'
## Tgarante

Plataforma de imóveis focada em anúncio, busca e gestão do mercado imobiliário.

### Destaques

- Catálogo de imóveis com busca e filtros
- Fluxos de anúncio e gestão para anunciantes
- Painel para acompanhar leads e inventário
MD,
                'sort_order' => 1,
                'featured' => true,
                'categories' => [self::CATEGORY_WEB],
                'technologies' => [self::TECH_LARAVEL, self::TECH_REACT, self::TECH_POSTGRES],
                'tags' => [self::TAG_PORTFOLIO],
            ],
            [
                'id' => self::PROJECT_FUI,
                'title' => 'Fui / AppFui',
                'slug' => 'fui-appfui',
                'description' => 'Rede social de avaliação de locais, onde usuários podem conhecer, avaliar e compartilhar experiências sobre lugares.',
                'content' => <<<'MD'
## Fui / AppFui

Rede social de avaliação de locais: descobrir, avaliar e partilhar experiências sobre lugares.

### Destaques

- Perfis de locais e avaliações da comunidade
- Feed e descoberta de lugares
- Experiência mobile-first para partilha no dia a dia
MD,
                'sort_order' => 2,
                'featured' => true,
                'categories' => [self::CATEGORY_WEB, self::CATEGORY_MOBILE],
                'technologies' => [self::TECH_REACT, self::TECH_HYPERF, self::TECH_POSTGRES],
                'tags' => [self::TAG_PORTFOLIO],
            ],
            [
                'id' => self::PROJECT_CAGE,
                'title' => 'Cage',
                'slug' => 'cage',
                'description' => 'Plataforma de gestão empresarial, na linha do Bitrix24, centralizando ferramentas de gerenciamento, comunicação e operação.',
                'content' => <<<'MD'
## Cage

Plataforma de gestão empresarial no espírito do Bitrix24: gestão, comunicação e operação num só lugar.

### Destaques

- Centralização de ferramentas de gestão
- Comunicação e colaboração entre equipas
- Operação do negócio em fluxos unificados
MD,
                'sort_order' => 3,
                'featured' => true,
                'categories' => [self::CATEGORY_WEB],
                'technologies' => [self::TECH_HYPERF, self::TECH_REACT, self::TECH_POSTGRES],
                'tags' => [self::TAG_PORTFOLIO],
            ],
        ];

        foreach ($projects as $project) {
            $categories = $project['categories'];
            $technologies = $project['technologies'];
            $tags = $project['tags'];
            unset($project['categories'], $project['technologies'], $project['tags']);

            if (Db::table('projects')->where('slug', $project['slug'])->exists()) {
                continue;
            }

            Db::table('projects')->insertOrIgnore([
                ...$project,
                'status' => 'published',
                'published_at' => $now,
                'views' => 0,
                'repository_url' => null,
                'demo_url' => null,
                'thumbnail_path' => null,
                'cover_path' => null,
                'image_path' => null,
                'owner_id' => $ownerId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($categories as $categoryId) {
                if (! Db::table('categories')->where('id', $categoryId)->exists()) {
                    continue;
                }
                Db::table('category_project')->insertOrIgnore([
                    'project_id' => $project['id'],
                    'category_id' => $categoryId,
                ]);
            }

            foreach ($technologies as $technologyId) {
                if (! Db::table('technologies')->where('id', $technologyId)->exists()) {
                    continue;
                }
                Db::table('project_technology')->insertOrIgnore([
                    'project_id' => $project['id'],
                    'technology_id' => $technologyId,
                ]);
            }

            foreach ($tags as $tagId) {
                if (! Db::table('tags')->where('id', $tagId)->exists()) {
                    continue;
                }
                Db::table('project_tag')->insertOrIgnore([
                    'project_id' => $project['id'],
                    'tag_id' => $tagId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $ids = [
            self::PROJECT_TGARANTE,
            self::PROJECT_FUI,
            self::PROJECT_CAGE,
        ];

        Db::table('category_project')->whereIn('project_id', $ids)->delete();
        Db::table('project_technology')->whereIn('project_id', $ids)->delete();
        Db::table('project_tag')->whereIn('project_id', $ids)->delete();
        Db::table('project_images')->whereIn('project_id', $ids)->delete();
        Db::table('projects')->whereIn('id', $ids)->delete();
    }
};
