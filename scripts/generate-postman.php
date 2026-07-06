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
$root = dirname(__DIR__);
$outDir = $root . '/docs/postman';
$envDir = $outDir . '/environments';

if (! is_dir($envDir) && ! mkdir($envDir, 0755, true) && ! is_dir($envDir)) {
    throw new RuntimeException('Cannot create output directory: ' . $envDir);
}

$collectionPreRequest = <<<'JS'
const baseUrl = pm.environment.get('baseUrl');
if (!baseUrl) {
    throw new Error('Select an environment (VictorDev — Local / Staging / Production).');
}

pm.request.headers.upsert({ key: 'Accept', value: 'application/json' });
pm.request.headers.upsert({ key: 'Accept-Language', value: pm.environment.get('locale') || 'pt_BR' });

const path = pm.request.url.getPath();
const method = pm.request.method;
const publicAuthPaths = ['auth/register', 'auth/login', 'auth/logout', 'auth/forgot-password', 'auth/reset-password'];
const isPublicAuth = publicAuthPaths.some(p => path.includes(p));
const isHealth = path.includes('health');
const isPublicGet = method === 'GET' && !path.includes('admin') && (
    path.includes('projects') || path.includes('pages') || path.includes('block-types') ||
    path.includes('site/settings') || path.includes('categories') ||
    path.includes('technologies') || path.includes('tags') ||
    path.includes('search') || /users\/[^/]+$/.test(path) ||
    path.endsWith('/api/v1') || path.endsWith('/api/v1/')
);

if (!isPublicAuth && !isHealth && !isPublicGet) {
    const token = pm.environment.get('accessToken');
    if (token) {
        pm.request.headers.upsert({ key: 'Authorization', value: `Bearer ${token}` });
    }
}

if (!pm.request.headers.has('X-Request-Id')) {
    pm.request.headers.add({ key: 'X-Request-Id', value: pm.variables.replaceIn('{{$guid}}') });
}
JS;

$collectionTest = <<<'JS'
pm.test('Response time acceptable', () => {
    const max = parseInt(pm.environment.get('maxResponseTimeMs') || '5000', 10);
    pm.expect(pm.response.responseTime).to.be.below(max);
});

if (pm.response.text() && pm.response.text().length > 0) {
    pm.test('Content-Type is JSON', () => {
        pm.expect(pm.response.headers.get('Content-Type')).to.include('application/json');
    });
}

pm.collectionVariables.set('lastStatusCode', String(pm.response.code));
pm.collectionVariables.set('lastResponseTime', String(pm.response.responseTime));
JS;

function script(array $lines): array
{
    return [
        'listen' => 'test',
        'script' => ['type' => 'text/javascript', 'exec' => $lines],
    ];
}

function preScript(array $lines): array
{
    return [
        'listen' => 'prerequest',
        'script' => ['type' => 'text/javascript', 'exec' => $lines],
    ];
}

function req(
    string $name,
    string $method,
    string $path,
    ?array $body = null,
    ?array $query = null,
    ?array $tests = null,
    ?array $pre = null,
    string $bodyMode = 'raw',
    ?array $formdata = null,
): array {
    $url = [
        'raw' => '{{baseUrl}}{{apiPrefix}}' . $path,
        'host' => ['{{baseUrl}}'],
        'path' => array_values(array_filter(explode('/', trim($path, '/')))),
    ];
    if ($query !== null) {
        $url['query'] = $query;
        $qs = http_build_query(array_column($query, 'value', 'key'));
        $url['raw'] .= '?' . $qs;
    }

    $request = [
        'method' => $method,
        'header' => [],
        'url' => $url,
    ];

    if ($bodyMode === 'raw' && $body !== null) {
        $request['body'] = [
            'mode' => 'raw',
            'raw' => json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'options' => ['raw' => ['language' => 'json']],
        ];
    } elseif ($formdata !== null) {
        $request['body'] = ['mode' => 'formdata', 'formdata' => $formdata];
    }

    $events = [];
    if ($pre !== null) {
        $events[] = preScript($pre);
    }
    if ($tests !== null) {
        $events[] = script($tests);
    }

    $item = ['name' => $name, 'request' => $request];
    if ($events !== []) {
        $item['event'] = $events;
    }

    return $item;
}

function folder(string $name, array $items, ?string $description = null): array
{
    $f = ['name' => $name, 'item' => $items];
    if ($description !== null) {
        $f['description'] = $description;
    }

    return $f;
}

$statusTest = fn (int $code, array $extra = []) => array_merge([
    "pm.test('Status {$code}', () => pm.response.to.have.status({$code}));",
], $extra);

$saveToken = [
    'const j = pm.response.json();',
    "if (j.access_token) pm.environment.set('accessToken', j.access_token);",
    "if (j.id && pm.info.requestName.includes('Register')) pm.environment.set('userId', j.id);",
];

$saveProject = [
    'const d = pm.response.json().data || pm.response.json();',
    "if (d.id) pm.environment.set('projectId', d.id);",
    "if (d.slug) pm.environment.set('projectSlug', d.slug);",
];

$savePage = [
    'const d = pm.response.json().data || pm.response.json();',
    "if (d.id) pm.environment.set('pageId', d.id);",
    "if (d.slug) pm.environment.set('pageSlug', d.slug);",
];

$saveUpload = [
    'const j = pm.response.json();',
    "if (j.id) pm.environment.set('uploadId', j.id);",
];

$saveImage = [
    'const j = pm.response.json();',
    "if (j.id) pm.environment.set('projectImageId', j.id);",
];

$saveFirstTaxonomy = [
    'const data = pm.response.json().data || [];',
    'if (data[0]?.id) {',
    "  if (pm.info.requestName.includes('Categories')) pm.environment.set('categoryId', data[0].id);",
    "  if (pm.info.requestName.includes('Technologies')) pm.environment.set('technologyId', data[0].id);",
    "  if (pm.info.requestName.includes('Tags')) pm.environment.set('tagId', data[0].id);",
    '}',
];

$collection = [
    'info' => [
        '_postman_id' => 'victordev-hyperf-api-v1',
        'name' => 'VictorDev — Hyperf API (v1)',
        'description' => "API REST VictorDev — Hyperf 3.x / DDD.\n\n**Base:** `{{baseUrl}}{{apiPrefix}}`\n\n**Docs:** docs/ROUTES.md\n\n**Setup:** `./hyper install` + select environment **VictorDev — Local**.\n\n**Seeds dev:** admin@victordev.com / VictorDev123!",
        'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
    ],
    'auth' => [
        'type' => 'bearer',
        'bearer' => [['key' => 'token', 'value' => '{{accessToken}}', 'type' => 'string']],
    ],
    'variable' => [
        ['key' => 'apiPrefix', 'value' => '/api/v1'],
        ['key' => 'lastStatusCode', 'value' => ''],
        ['key' => 'lastResponseTime', 'value' => ''],
    ],
    'event' => [
        ['listen' => 'prerequest', 'script' => ['type' => 'text/javascript', 'exec' => explode("\n", $collectionPreRequest)]],
        ['listen' => 'test', 'script' => ['type' => 'text/javascript', 'exec' => explode("\n", $collectionTest)]],
    ],
    'item' => [
        folder('00 — Setup & Health', [
            req('Index — GET', 'GET', '/', null, [['key' => 'user', 'value' => 'Postman']], $statusTest(200)),
            req('Health — Live', 'GET', '/health/live', null, null, $statusTest(200)),
            req('Health — Ready', 'GET', '/health/ready', null, null, [
                "pm.test('Status 200 or 503', () => pm.expect([200, 503]).to.include(pm.response.code));",
            ]),
            req('Health — Aggregate', 'GET', '/health', null, null, [
                "pm.test('Status 200 or 503', () => pm.expect([200, 503]).to.include(pm.response.code));",
            ]),
        ], 'Probes without authentication.'),

        folder('01 — Auth', [
            folder('Success', [
                req('Register — success', 'POST', '/auth/register', [
                    'name' => 'Postman User',
                    'email' => 'postman-{{$timestamp}}@example.com',
                    'password' => '{{testUserPassword}}',
                    'password_confirmation' => '{{testUserPassword}}',
                ], null, array_merge($statusTest(200), $saveToken, [
                    "pm.test('Has roles', () => pm.expect(pm.response.json().roles).to.be.an('array'));",
                ])),
                req('Login — Admin', 'POST', '/auth/login', [
                    'email' => '{{adminEmail}}',
                    'password' => '{{adminPassword}}',
                ], null, array_merge($statusTest(200), $saveToken, [
                    "pm.test('Has admin role', () => pm.expect(pm.response.json().roles).to.include('admin'));",
                ])),
                req('Login — Manager', 'POST', '/auth/login', [
                    'email' => '{{managerEmail}}',
                    'password' => '{{managerPassword}}',
                ], null, array_merge($statusTest(200), $saveToken)),
                req('Refresh Token', 'POST', '/auth/refresh', null, null, array_merge($statusTest(200), $saveToken)),
                req('Logout', 'POST', '/auth/logout', null, null, $statusTest(200)),
                req('Forgot Password', 'POST', '/auth/forgot-password', [
                    'email' => '{{adminEmail}}',
                ], null, $statusTest(200)),
                req('Change Password', 'POST', '/auth/change-password', [
                    'current_password' => '{{adminPassword}}',
                    'password' => '{{adminPassword}}',
                    'password_confirmation' => '{{adminPassword}}',
                ], null, $statusTest(200)),
            ]),
            folder('Errors', [
                req('Login — invalid credentials (401)', 'POST', '/auth/login', [
                    'email' => '{{adminEmail}}',
                    'password' => 'WrongPass1',
                ], null, $statusTest(401)),
                req('Register — validation (422)', 'POST', '/auth/register', [
                    'name' => '',
                    'email' => 'not-an-email',
                    'password' => 'weak',
                ], null, array_merge($statusTest(422), [
                    "pm.test('Has errors', () => pm.expect(pm.response.json().errors).to.be.an('object'));",
                ])),
                req('Reset Password — invalid code (401)', 'POST', '/auth/reset-password', [
                    'code' => '000000',
                    'password' => 'Secret1a',
                    'password_confirmation' => 'Secret1a',
                ], null, $statusTest(401)),
                req('Users/me — no token (401)', 'GET', '/users/me', null, null, $statusTest(401), [
                    "pm.request.headers.remove('Authorization');",
                ]),
            ]),
        ], 'Authentication flows. Run **Login — Admin** before Admin folders.'),

        folder('02 — Users (Public)', [
            req('GET /users/me', 'GET', '/users/me', null, null, array_merge($statusTest(200), [
                "pm.test('Has email', () => pm.expect(pm.response.json().email).to.be.a('string'));",
            ])),
            req('GET /users/{id} — success', 'GET', '/users/{{userId}}', null, null, $statusTest(200)),
            req('GET /users/{id} — not found (404)', 'GET', '/users/a0000001-0000-4000-8000-000000000099', null, null, $statusTest(404)),
        ]),

        folder('03 — Portfolio (Public)', [
            req('Pages — Home', 'GET', '/pages/home', null, null, array_merge($statusTest(200), [
                "pm.test('Has data', () => pm.expect(pm.response.json().data).to.be.an('object'));",
            ], $savePage)),
            req('Pages — List', 'GET', '/pages', null, [
                ['key' => 'page', 'value' => '1'],
                ['key' => 'per_page', 'value' => '15'],
            ], $statusTest(200)),
            req('Pages — Show by slug', 'GET', '/pages/{{pageSlug}}', null, null, array_merge($statusTest(200), [
                "pm.test('Has data', () => pm.expect(pm.response.json().data).to.be.an('object'));",
            ])),
            req('Block Types — list', 'GET', '/block-types', null, null, array_merge($statusTest(200), [
                "pm.test('Has block types', () => pm.expect(pm.response.json().data).to.be.an('array'));",
            ])),
            req('Site Settings — public', 'GET', '/site/settings', null, null, array_merge($statusTest(200), [
                "pm.test('Has data', () => pm.expect(pm.response.json().data).to.be.an('object'));",
            ])),
            req('Contact — submit', 'POST', '/contact', [
                'name' => 'Postman User',
                'email' => 'postman@example.com',
                'subject' => 'Test from Postman',
                'message' => 'Hello from the Postman collection test.',
            ], null, array_merge($statusTest(200), [
                "pm.test('Generic success', () => pm.expect(pm.response.json().message).to.be.a('string'));",
            ])),
            req('Pages — Contact', 'GET', '/pages/contato', null, null, $statusTest(200)),
            req('Projects — List', 'GET', '/projects', null, [
                ['key' => 'page', 'value' => '1'],
                ['key' => 'per_page', 'value' => '15'],
            ], $statusTest(200)),
            req('Projects — List featured', 'GET', '/projects', null, [
                ['key' => 'featured', 'value' => 'true'],
                ['key' => 'sort', 'value' => 'published_at'],
                ['key' => 'direction', 'value' => 'desc'],
            ], $statusTest(200)),
            req('Projects — Show by slug', 'GET', '/projects/{{projectSlug}}', null, null, array_merge($statusTest(200), [
                "pm.test('Has data', () => pm.expect(pm.response.json().data).to.be.an('object'));",
            ])),
            req('Projects — Related', 'GET', '/projects/{{projectSlug}}/related', null, null, $statusTest(200)),
            req('Search — projects', 'GET', '/search', null, [
                ['key' => 'q', 'value' => 'portfolio'],
                ['key' => 'page', 'value' => '1'],
            ], $statusTest(200)),
            req('Categories — list', 'GET', '/categories', null, null, array_merge($statusTest(200), $saveFirstTaxonomy)),
            req('Technologies — list', 'GET', '/technologies', null, null, array_merge($statusTest(200), $saveFirstTaxonomy)),
            req('Tags — list', 'GET', '/tags', null, null, array_merge($statusTest(200), $saveFirstTaxonomy)),
            req('Projects — Show — not found (404)', 'GET', '/projects/non-existent-slug-404', null, null, $statusTest(404)),
            req('Pages — Show — not found (404)', 'GET', '/pages/non-existent-slug-404', null, null, $statusTest(404)),
        ]),

        folder('04 — Admin — Users', [
            req('List users', 'GET', '/admin/users', null, [
                ['key' => 'page', 'value' => '1'],
                ['key' => 'per_page', 'value' => '15'],
                ['key' => 'search', 'value' => 'admin'],
            ], $statusTest(200)),
            req('Create user', 'POST', '/admin/users', [
                'name' => 'Admin Created',
                'email' => 'admin-created-{{$timestamp}}@example.com',
                'password' => '{{testUserPassword}}',
                'password_confirmation' => '{{testUserPassword}}',
            ], null, array_merge($statusTest(200), [
                "if (pm.response.json().id) pm.environment.set('adminCreatedUserId', pm.response.json().id);",
            ])),
            req('Show user', 'GET', '/admin/users/{{userId}}', null, null, $statusTest(200)),
            req('Update user', 'PUT', '/admin/users/{{userId}}', [
                'name' => 'Updated Name',
                'email' => '{{adminEmail}}',
            ], null, $statusTest(200)),
            req('Show user — not found (404)', 'GET', '/admin/users/a0000001-0000-4000-8000-000000000099', null, null, $statusTest(404)),
        ], 'Requires Login — Admin. Permission: users.*'),

        folder('05 — Admin — RBAC', [
            req('List roles', 'GET', '/admin/roles', null, null, $statusTest(200)),
            req('List permissions', 'GET', '/admin/permissions', null, null, array_merge($statusTest(200), [
                'const data = pm.response.json().data || [];',
                "if (data[0]?.id) pm.environment.set('permissionId', data[0].id);",
            ])),
            req('Create role', 'POST', '/admin/roles', [
                'name' => 'Editor Postman',
                'slug' => 'editor-postman-{{$timestamp}}',
            ], null, array_merge($statusTest(200), [
                "if (pm.response.json().id) pm.environment.set('roleId', pm.response.json().id);",
            ])),
            req('Sync role permissions', 'PUT', '/admin/roles/{{roleId}}/permissions', [
                'permission_ids' => ['{{permissionId}}'],
            ], null, $statusTest(200)),
            req('Sync user roles', 'PUT', '/admin/users/{{userId}}/roles', [
                'role_ids' => ['{{roleId}}'],
            ], null, $statusTest(200)),
        ]),

        folder('06 — Admin — Uploads', [
            req('Upload file (multipart)', 'POST', '/admin/uploads', null, null, array_merge($statusTest(200), $saveUpload, [
                "pm.test('Has processing_status', () => pm.expect(pm.response.json().processing_status).to.be.a('string');",
            ]), null, 'raw', [
                ['key' => 'file', 'type' => 'file', 'src' => []],
            ]),
        ], 'Select a local image file in the form-data field `file`.'),

        folder('07 — Admin — Projects', [
            folder('CRUD', [
                req('Statistics', 'GET', '/admin/projects/statistics', null, null, $statusTest(200)),
                req('List projects', 'GET', '/admin/projects', null, [
                    ['key' => 'page', 'value' => '1'],
                    ['key' => 'status', 'value' => 'draft'],
                ], $statusTest(200)),
                req('Create project', 'POST', '/admin/projects', [
                    'title' => 'Postman Project {{$timestamp}}',
                    'slug' => 'postman-project-{{$timestamp}}',
                    'description' => 'Created via Postman collection',
                    'content' => "# Postman\n\nTest project.",
                    'repository_url' => 'https://github.com/example/repo',
                    'demo_url' => 'https://demo.example.com',
                    'status' => 'draft',
                    'featured' => false,
                    'categories' => [],
                    'technologies' => [],
                    'tags' => [],
                ], null, array_merge($statusTest(200), $saveProject)),
                req('Show project', 'GET', '/admin/projects/{{projectId}}', null, null, $statusTest(200)),
                req('Patch project', 'PATCH', '/admin/projects/{{projectId}}', [
                    'featured' => true,
                    'description' => 'Updated via PATCH',
                ], null, array_merge($statusTest(200), $saveProject)),
                req('Update project (full)', 'PUT', '/admin/projects/{{projectId}}', [
                    'title' => 'Postman Project Full Update',
                    'slug' => '{{projectSlug}}',
                    'description' => 'Full PUT update',
                    'content' => '# Updated',
                    'repository_url' => 'https://github.com/example/repo',
                    'demo_url' => 'https://demo.example.com',
                    'status' => 'draft',
                    'featured' => true,
                    'categories' => [],
                    'technologies' => [],
                    'tags' => [],
                ], null, $statusTest(200)),
            ]),
            folder('Lifecycle', [
                req('Publish', 'PATCH', '/admin/projects/{{projectId}}/publish', [], null, array_merge($statusTest(200), $saveProject, [
                    "pm.test('Published', () => pm.expect(pm.response.json().data.status).to.eql('published'));",
                ])),
                req('Archive', 'PATCH', '/admin/projects/{{projectId}}/archive', null, null, array_merge($statusTest(200), [
                    "pm.test('Archived', () => pm.expect(pm.response.json().data.status).to.eql('archived'));",
                ])),
                req('Draft', 'PATCH', '/admin/projects/{{projectId}}/draft', null, null, array_merge($statusTest(200), [
                    "pm.test('Draft', () => pm.expect(pm.response.json().data.status).to.eql('draft'));",
                ])),
                req('Publish again', 'PATCH', '/admin/projects/{{projectId}}/publish', null, null, $statusTest(200)),
                req('Duplicate', 'POST', '/admin/projects/{{projectId}}/duplicate', null, null, array_merge($statusTest(200), [
                    'const d = pm.response.json().data;',
                    "if (d?.id) pm.environment.set('projectCopyId', d.id);",
                ])),
                req('Soft delete', 'DELETE', '/admin/projects/{{projectCopyId}}', null, null, $statusTest(200)),
                req('Restore', 'PATCH', '/admin/projects/{{projectCopyId}}/restore', null, null, $statusTest(200)),
                req('Force delete copy', 'DELETE', '/admin/projects/{{projectCopyId}}/force', null, null, $statusTest(200)),
                req('Reorder projects', 'PATCH', '/admin/projects/order', [
                    'projects' => [
                        ['id' => '{{projectId}}', 'order' => 1],
                    ],
                ], null, $statusTest(200)),
            ]),
            folder('Images & Taxonomies', [
                req('Add image to project', 'POST', '/admin/projects/{{projectId}}/images', [
                    'image_id' => '{{uploadId}}',
                    'caption' => 'Postman screenshot',
                ], null, array_merge($statusTest(200), $saveImage)),
                req('Reorder images', 'PATCH', '/admin/projects/{{projectId}}/images/order', [
                    'images' => [
                        ['id' => '{{projectImageId}}', 'order' => 1],
                    ],
                ], null, $statusTest(200)),
                req('Set thumbnail', 'PATCH', '/admin/projects/{{projectId}}/thumbnail', [
                    'image_id' => '{{uploadId}}',
                ], null, $statusTest(200)),
                req('Set cover', 'PATCH', '/admin/projects/{{projectId}}/cover', [
                    'image_id' => '{{uploadId}}',
                ], null, $statusTest(200)),
                req('Sync categories', 'PUT', '/admin/projects/{{projectId}}/categories', [
                    'categories' => ['{{categoryId}}'],
                ], null, $statusTest(200)),
                req('Sync technologies', 'PUT', '/admin/projects/{{projectId}}/technologies', [
                    'technologies' => ['{{technologyId}}'],
                ], null, $statusTest(200)),
                req('Sync tags', 'PUT', '/admin/projects/{{projectId}}/tags', [
                    'tags' => ['{{tagId}}'],
                ], null, $statusTest(200)),
                req('Remove image', 'DELETE', '/admin/projects/{{projectId}}/images/{{projectImageId}}', null, null, $statusTest(200)),
            ]),
            folder('Errors', [
                req('Show — not found (404)', 'GET', '/admin/projects/a0000001-0000-4000-8000-000000000099', null, null, $statusTest(404)),
                req('Create — validation (422)', 'POST', '/admin/projects', [
                    'title' => '',
                ], null, $statusTest(422)),
            ]),
        ]),

        folder('08 — Admin — Pages', [
            folder('CRUD', [
                req('List pages', 'GET', '/admin/pages', null, [
                    ['key' => 'page', 'value' => '1'],
                    ['key' => 'per_page', 'value' => '15'],
                ], $statusTest(200)),
                req('Create page', 'POST', '/admin/pages', [
                    'title' => 'Postman Page {{$timestamp}}',
                    'slug' => 'postman-page-{{$timestamp}}',
                    'layout' => 'default',
                    'is_home' => false,
                    'status' => 'draft',
                    'seo' => [
                        'meta_title' => 'Postman Page',
                        'meta_description' => 'Created via Postman collection',
                    ],
                ], null, array_merge($statusTest(200), $savePage)),
                req('Show page', 'GET', '/admin/pages/{{pageId}}', null, null, $statusTest(200)),
                req('Patch page', 'PATCH', '/admin/pages/{{pageId}}', [
                    'title' => 'Postman Page Updated',
                ], null, array_merge($statusTest(200), $savePage)),
                req('Update page (full)', 'PUT', '/admin/pages/{{pageId}}', [
                    'title' => 'Postman Page Full Update',
                    'slug' => '{{pageSlug}}',
                    'layout' => 'default',
                    'is_home' => false,
                    'status' => 'draft',
                    'seo' => [
                        'meta_title' => 'Postman Page Full',
                        'meta_description' => 'Full PUT update',
                    ],
                ], null, $statusTest(200)),
            ]),
            folder('Lifecycle', [
                req('Publish page', 'PATCH', '/admin/pages/{{pageId}}/publish', null, null, array_merge($statusTest(200), $savePage, [
                    "pm.test('Published', () => pm.expect(pm.response.json().data.status).to.eql('published'));",
                ])),
                req('Archive page', 'PATCH', '/admin/pages/{{pageId}}/archive', null, null, array_merge($statusTest(200), [
                    "pm.test('Archived', () => pm.expect(pm.response.json().data.status).to.eql('archived'));",
                ])),
                req('Draft page', 'PATCH', '/admin/pages/{{pageId}}/draft', null, null, array_merge($statusTest(200), [
                    "pm.test('Draft', () => pm.expect(pm.response.json().data.status).to.eql('draft'));",
                ])),
                req('Publish page again', 'PATCH', '/admin/pages/{{pageId}}/publish', null, null, $statusTest(200)),
                req('Duplicate page', 'POST', '/admin/pages/{{pageId}}/duplicate', null, null, array_merge($statusTest(200), [
                    'const d = pm.response.json().data;',
                    "if (d?.id) pm.environment.set('pageCopyId', d.id);",
                ])),
                req('Soft delete page copy', 'DELETE', '/admin/pages/{{pageCopyId}}', null, null, $statusTest(200)),
                req('Restore page copy', 'PATCH', '/admin/pages/{{pageCopyId}}/restore', null, null, $statusTest(200)),
                req('Force delete page copy', 'DELETE', '/admin/pages/{{pageCopyId}}/force', null, null, $statusTest(200)),
                req('Reorder pages', 'PATCH', '/admin/pages/order', [
                    'items' => [
                        ['id' => '{{pageId}}', 'sort_order' => 1],
                    ],
                ], null, $statusTest(200)),
            ]),
            folder('Blocks', [
                req('Sync page blocks', 'PUT', '/admin/pages/{{pageId}}/blocks', [
                    'blocks' => [
                        [
                            'type' => 'hero',
                            'payload' => [
                                'headline' => 'Postman Hero',
                                'subheadline' => 'Page Builder test block',
                            ],
                            'settings' => [],
                        ],
                        [
                            'type' => 'markdown',
                            'payload' => [
                                'content' => "# Postman\n\nSynced via collection.",
                            ],
                            'settings' => [],
                        ],
                    ],
                ], null, array_merge($statusTest(200), [
                    "pm.test('Has blocks', () => pm.expect(pm.response.json().data.blocks).to.be.an('array'));",
                ])),
            ]),
            folder('Errors', [
                req('Show page — not found (404)', 'GET', '/admin/pages/a0000001-0000-4000-8000-000000000099', null, null, $statusTest(404)),
                req('Create page — validation (422)', 'POST', '/admin/pages', [
                    'title' => '',
                ], null, $statusTest(422)),
            ]),
        ], 'Requires Login — Admin. Permission: pages.*'),

        folder('09 — Admin — Site Settings', [
            req('Get site settings', 'GET', '/admin/site/settings', null, null, array_merge($statusTest(200), [
                "pm.test('Has data', () => pm.expect(pm.response.json().data).to.be.an('object'));",
            ])),
            req('Update site settings', 'PUT', '/admin/site/settings', [
                'seo' => [
                    'site_name' => 'Victor Dev',
                    'default_meta_description' => 'Portfolio updated via Postman',
                    'locale' => 'pt_BR',
                ],
                'nav' => [
                    ['label' => 'Início', 'href' => '/'],
                    ['label' => 'Projetos', 'href' => '/projects'],
                ],
            ], null, $statusTest(200)),
        ], 'Requires Login — Admin. Permission: site.update'),

        folder('10 — Admin — Contact', [
            req('List contact messages', 'GET', '/admin/contact/messages', null, [
                ['key' => 'page', 'value' => '1'],
                ['key' => 'per_page', 'value' => '15'],
                ['key' => 'status', 'value' => 'new'],
            ], null, array_merge($statusTest(200), [
                "const d = pm.response.json().data; if (d?.[0]?.id) pm.environment.set('contactMessageId', d[0].id);",
            ])),
            req('Show contact message', 'GET', '/admin/contact/messages/{{contactMessageId}}', null, null, array_merge($statusTest(200), [
                "pm.test('Marks read', () => pm.expect(['read','archived']).to.include(pm.response.json().data.status));",
            ])),
            req('Archive contact message', 'PATCH', '/admin/contact/messages/{{contactMessageId}}', [
                'status' => 'archived',
            ], null, $statusTest(200)),
            req('Show contact message — not found (404)', 'GET', '/admin/contact/messages/a0000001-0000-4000-8000-000000000099', null, null, $statusTest(404)),
        ], 'Requires Login — Admin. Permissions: contact.view, contact.update'),

        folder('99 — Flows (E2E)', [
            req('Flow 1 — Login Admin', 'POST', '/auth/login', [
                'email' => '{{adminEmail}}',
                'password' => '{{adminPassword}}',
            ], null, array_merge($statusTest(200), $saveToken)),
            req('Flow 2 — Create Project', 'POST', '/admin/projects', [
                'title' => 'E2E Flow {{$timestamp}}',
                'slug' => 'e2e-flow-{{$timestamp}}',
                'description' => 'E2E',
                'content' => '# E2E',
                'status' => 'draft',
                'featured' => false,
                'categories' => [],
                'technologies' => [],
                'tags' => [],
            ], null, array_merge($statusTest(200), $saveProject)),
            req('Flow 3 — Publish', 'PATCH', '/admin/projects/{{projectId}}/publish', null, null, $statusTest(200)),
            req('Flow 4 — Public show', 'GET', '/projects/{{projectSlug}}', null, null, $statusTest(200)),
            req('Flow 5 — Public search', 'GET', '/search', null, [
                ['key' => 'q', 'value' => 'e2e'],
            ], $statusTest(200)),
        ], 'Run folder sequentially with Collection Runner.'),
    ],
];

file_put_contents(
    $outDir . '/VictorDev-Hyperf-API.postman_collection.json',
    json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
);

function envFile(string $name, array $values): void
{
    global $envDir;
    $data = [
        'name' => $name,
        'values' => array_map(static fn (array $v): array => array_merge([
            'type' => 'default',
            'enabled' => true,
        ], $v), $values),
        '_postman_variable_scope' => 'environment',
    ];
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
    file_put_contents($envDir . '/' . trim($slug, '-') . '.postman_environment.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
}

/** @param array<int, array<string, mixed>> ...$groups */
function mergeEnvValues(array ...$groups): array
{
    $byKey = [];
    foreach ($groups as $group) {
        foreach ($group as $item) {
            $byKey[(string) $item['key']] = $item;
        }
    }

    return array_values($byKey);
}

$commonEnv = [
    ['key' => 'apiPrefix', 'value' => '/api/v1'],
    ['key' => 'adminEmail', 'value' => 'admin@victordev.com'],
    ['key' => 'adminPassword', 'value' => 'VictorDev123!', 'type' => 'secret'],
    ['key' => 'managerEmail', 'value' => 'manager@victordev.com'],
    ['key' => 'managerPassword', 'value' => 'VictorDev123!', 'type' => 'secret'],
    ['key' => 'testUserPassword', 'value' => 'Secret1a', 'type' => 'secret'],
    ['key' => 'accessToken', 'value' => '', 'type' => 'secret'],
    ['key' => 'userId', 'value' => ''],
    ['key' => 'projectId', 'value' => ''],
    ['key' => 'projectSlug', 'value' => ''],
    ['key' => 'projectCopyId', 'value' => ''],
    ['key' => 'pageId', 'value' => ''],
    ['key' => 'pageSlug', 'value' => ''],
    ['key' => 'pageCopyId', 'value' => ''],
    ['key' => 'uploadId', 'value' => ''],
    ['key' => 'projectImageId', 'value' => ''],
    ['key' => 'categoryId', 'value' => ''],
    ['key' => 'technologyId', 'value' => ''],
    ['key' => 'tagId', 'value' => ''],
    ['key' => 'roleId', 'value' => ''],
    ['key' => 'permissionId', 'value' => ''],
    ['key' => 'adminCreatedUserId', 'value' => ''],
    ['key' => 'resetCode', 'value' => ''],
    ['key' => 'locale', 'value' => 'pt_BR'],
    ['key' => 'maxResponseTimeMs', 'value' => '5000'],
];

envFile('VictorDev — Local', mergeEnvValues($commonEnv, [
    ['key' => 'baseUrl', 'value' => 'http://127.0.0.1:9501'],
]));

envFile('VictorDev — Staging', mergeEnvValues($commonEnv, [
    ['key' => 'baseUrl', 'value' => 'https://staging-api.victordev.com.br'],
]));

envFile('VictorDev — Production', mergeEnvValues($commonEnv, [
    ['key' => 'baseUrl', 'value' => 'https://api.victordev.com.br'],
    ['key' => 'adminPassword', 'value' => '', 'type' => 'secret'],
    ['key' => 'managerPassword', 'value' => '', 'type' => 'secret'],
    ['key' => 'testUserPassword', 'value' => '', 'type' => 'secret'],
]));

echo "Generated:\n";
echo "  - docs/postman/VictorDev-Hyperf-API.postman_collection.json\n";
echo "  - docs/postman/environments/*.postman_environment.json\n";
