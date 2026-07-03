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
return [
    'forbidden' => 'Proibido',
    'validation_failed' => 'Falha na validação',
    'unauthorized' => 'Não autorizado',
    'internal_server_error' => 'Erro interno do servidor.',
    'email_already_registered' => 'Já existe uma conta com este e-mail.',
    'registration_successful' => 'Cadastro realizado com sucesso.',
    'invalid_email_or_password' => 'E-mail ou senha inválidos.',
    'logout_stateless' => 'O token é descartado no cliente. Este endpoint não altera estado no servidor (API stateless).',
    'forgot_password_generic' => 'Se existir uma conta para esse e-mail, enviamos as instruções de redefinição de senha.',
    'reset_invalid_code' => 'Código de verificação inválido ou expirado.',
    'reset_success' => 'Senha redefinida. Você já pode entrar com a nova senha.',
    'current_password_incorrect' => 'A senha atual está incorreta.',
    'password_updated' => 'Senha atualizada.',
    'user_not_found' => 'Usuário não encontrado',
    'rbac_role_created' => 'Role criada.',
    'rbac_role_deleted' => 'Role deletada.',
    'rbac_role_permissions_updated' => 'Permissões da role atualizadas.',
    'rbac_user_roles_updated' => 'Roles do usuário atualizadas.',
    'admin_user_created' => 'Utilizador criado.',
    'admin_user_updated' => 'Utilizador atualizado.',
    'project_not_found' => 'Projeto não encontrado.',
    'project_slug_taken' => 'Slug de projeto já em uso.',
    'project_created' => 'Projeto criado.',
    'project_updated' => 'Projeto atualizado.',
    'project_deleted' => 'Projeto eliminado.',
    'project_published' => 'Projeto publicado.',
    'project_archived' => 'Projeto arquivado.',
    'projects_reordered' => 'Ordem dos projetos atualizada.',
    'page_not_found' => 'Página não encontrada.',
    'page_slug_taken' => 'Slug de página já em uso.',
    'page_created' => 'Página criada.',
    'page_updated' => 'Página atualizada.',
    'page_deleted' => 'Página eliminada.',
    'page_published' => 'Página publicada.',
    'page_archived' => 'Página arquivada.',
    'pages_reordered' => 'Ordem das páginas atualizada.',
    'page_blocks_synced' => 'Blocos da página atualizados.',
    'invalid_block_payload' => 'Payload de bloco inválido.',
    'site_settings_updated' => 'Configurações do site atualizadas.',
    'post_not_found' => 'Post não encontrado.',
    'post_created' => 'Post criado.',
    'post_updated' => 'Post atualizado.',
    'post_deleted' => 'Post eliminado.',
    'post_published' => 'Post publicado.',
];
