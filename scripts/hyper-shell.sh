#!/usr/bin/env bash
# Função shell para `hyper` sem ./ — estilo Sail.
# Adicione ao ~/.zshrc ou ~/.bashrc:
#   eval "$(cat /caminho/para/hyperf_p/scripts/hyper-shell.sh)"
# Ou, dentro do projecto (com direnv): `direnv allow` (ver .envrc)

_hyper_find_root() {
  local dir="${PWD}"
  while [[ "${dir}" != "/" ]]; do
    if [[ -x "${dir}/vendor/bin/hyper" ]]; then
      echo "${dir}"
      return 0
    fi
    if [[ -x "${dir}/hyper" ]]; then
      echo "${dir}"
      return 0
    fi
    dir="$(dirname "${dir}")"
  done
  return 1
}

hyper() {
  local root
  if ! root="$(_hyper_find_root)"; then
    echo "hyper: projecto não encontrado (corra composer install ou entre na pasta do repo)" >&2
    return 127
  fi

  if [[ -x "${root}/vendor/bin/hyper" ]]; then
    (cd "${root}" && vendor/bin/hyper "$@")
  else
    (cd "${root}" && ./hyper "$@")
  fi
}
