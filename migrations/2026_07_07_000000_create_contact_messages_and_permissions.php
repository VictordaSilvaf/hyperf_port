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
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;

return new class extends Migration {
    private const SITE_SETTINGS_ID = '00000000-0000-4000-8000-000000000001';

    private const ROLE_ADMIN = 'a0000001-0000-4000-8000-000000000001';

    private const ROLE_MANAGER = 'a0000002-0000-4000-8000-000000000001';

    private const PERM_CONTACT_VIEW = 'b0000050-0000-4000-8000-000000000001';

    private const PERM_CONTACT_UPDATE = 'b0000051-0000-4000-8000-000000000001';

    private const PAGE_CONTATO_ID = 'c0000001-0000-4000-8000-000000000001';

    private const BLOCK_HERO_ID = 'c0000002-0000-4000-8000-000000000001';

    private const BLOCK_MARKDOWN_ID = 'c0000003-0000-4000-8000-000000000001';

    private const BLOCK_CONTACT_FORM_ID = 'c0000004-0000-4000-8000-000000000001';

    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('name', 200);
            $table->string('email', 255)->index();
            $table->string('subject', 300)->nullable();
            $table->text('body');
            $table->string('status', 20)->default('new');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['status', 'created_at']);
            $table->index('created_at');
        });

        if (Schema::hasTable('site_settings') && ! Schema::hasColumn('site_settings', 'contact')) {
            Schema::table('site_settings', function (Blueprint $table) {
                $table->jsonb('contact')->nullable()->after('seo');
            });
        }

        $now = date('Y-m-d H:i:s');
        $defaultContact = json_encode([
            'email' => null,
            'phone' => null,
            'whatsapp' => null,
            'address' => null,
            'notification_email' => null,
        ]);

        if (Schema::hasTable('site_settings')) {
            Db::table('site_settings')
                ->where('id', self::SITE_SETTINGS_ID)
                ->whereNull('contact')
                ->update(['contact' => $defaultContact, 'updated_at' => $now]);
        }

        $permissions = [
            ['id' => self::PERM_CONTACT_VIEW, 'slug' => 'contact.view', 'description' => 'Listar mensagens de contacto'],
            ['id' => self::PERM_CONTACT_UPDATE, 'slug' => 'contact.update', 'description' => 'Gerir estado das mensagens de contacto'],
        ];

        foreach ($permissions as $p) {
            Db::table('permissions')->insertOrIgnore([
                'id' => $p['id'],
                'slug' => $p['slug'],
                'description' => $p['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([self::PERM_CONTACT_VIEW, self::PERM_CONTACT_UPDATE] as $permId) {
            Db::table('role_permission')->insertOrIgnore([
                'role_id' => self::ROLE_ADMIN,
                'permission_id' => $permId,
            ]);
            Db::table('role_permission')->insertOrIgnore([
                'role_id' => self::ROLE_MANAGER,
                'permission_id' => $permId,
            ]);
        }

        if (Schema::hasTable('pages')) {
            Db::table('pages')->insertOrIgnore([
                'id' => self::PAGE_CONTATO_ID,
                'title' => 'Contacto',
                'slug' => 'contato',
                'status' => 'draft',
                'layout' => 'default',
                'seo' => json_encode([
                    'meta_title' => 'Contacto',
                    'meta_description' => 'Entre em contacto',
                ]),
                'is_home' => false,
                'sort_order' => 10,
                'published_at' => null,
                'deleted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (Schema::hasTable('page_blocks')) {
            $blocks = [
                [
                    'id' => self::BLOCK_HERO_ID,
                    'page_id' => self::PAGE_CONTATO_ID,
                    'type' => 'hero',
                    'sort_order' => 0,
                    'payload' => json_encode([
                        'headline' => 'Entre em contacto',
                        'subheadline' => 'Envie uma mensagem e responderei o mais breve possível.',
                    ]),
                    'settings' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => self::BLOCK_MARKDOWN_ID,
                    'page_id' => self::PAGE_CONTATO_ID,
                    'type' => 'markdown',
                    'sort_order' => 1,
                    'payload' => json_encode([
                        'content' => 'Preencha o formulário abaixo ou utilize os contactos disponíveis nas definições do site.',
                    ]),
                    'settings' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => self::BLOCK_CONTACT_FORM_ID,
                    'page_id' => self::PAGE_CONTATO_ID,
                    'type' => 'contact_form',
                    'sort_order' => 2,
                    'payload' => json_encode([
                        'title' => 'Formulário de contacto',
                        'submit_label' => 'Enviar mensagem',
                        'success_message' => 'Mensagem enviada com sucesso!',
                        'show_subject' => true,
                    ]),
                    'settings' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ];

            foreach ($blocks as $block) {
                Db::table('page_blocks')->insertOrIgnore($block);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('page_blocks')) {
            Db::table('page_blocks')->where('page_id', self::PAGE_CONTATO_ID)->delete();
        }

        if (Schema::hasTable('pages')) {
            Db::table('pages')->where('id', self::PAGE_CONTATO_ID)->delete();
        }

        $permIds = [self::PERM_CONTACT_VIEW, self::PERM_CONTACT_UPDATE];
        Db::table('role_permission')->whereIn('permission_id', $permIds)->delete();
        Db::table('permissions')->whereIn('id', $permIds)->delete();

        if (Schema::hasTable('site_settings') && Schema::hasColumn('site_settings', 'contact')) {
            Schema::table('site_settings', function (Blueprint $table) {
                $table->dropColumn('contact');
            });
        }

        Schema::dropIfExists('contact_messages');
    }
};
