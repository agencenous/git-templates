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

### Options

| Option | Alias | Description |
|---|---|---|
| `--project-dir` | `-d` | Chemin racine du projet cible (par defaut : repertoire courant) |

Exemple avec un chemin personnalise :

```bash
vendor/bin/gitlab-templates -d /chemin/vers/mon-projet
```

## Templates inclus

- **Default.md** : Template de feature request structure (cas d'utilisation, description technique, modules concernes, points de vigilance, estimations).

## Licence

[GPL-3.0](LICENSE)
