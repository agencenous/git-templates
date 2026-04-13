# GitLab Templates

Outil CLI pour installer des templates d'issues GitLab standardises dans vos projets.

## Installation

```bash
composer require agencenous/gitlab-templates
```

## Utilisation

Depuis la racine de votre projet :

```bash
vendor/bin/gitlab-templates
```

Les templates sont copies dans `.gitlab/issue_templates/`.

### Locale

La locale est resolue dans cet ordre :

1. Option `--locale` / `-l`
2. Variable d'environnement `LANGUAGE`
3. Par defaut : `en_US`

Locales disponibles : `fr_FR`, `en_US`.

```bash
# Templates en francais
vendor/bin/gitlab-templates -l fr_FR

# Via variable d'environnement
LANGUAGE=fr_FR vendor/bin/gitlab-templates
```

### Options

| Option | Alias | Description |
|---|---|---|
| `--project-dir` | `-d` | Chemin racine du projet cible (par defaut : repertoire courant) |
| `--locale` | `-l` | Locale a utiliser (par defaut : variable `LANGUAGE`, puis `en_US`) |

## Templates inclus

- **Default.md** : Template de feature request structure (cas d'utilisation, description technique, modules concernes, points de vigilance, estimations).

## Licence

[GPL-3.0](LICENSE)
