# Git Templates

Tool for creating/syncing issue templates in git projects

**Version: 1.0.0**

Available templates:
- **Default.md**: Template for a feature request (use case, technical description, impacted modules, points of concern, estimations).
- **Incident.md**: Template for a bug

## Install

```bash
composer require agencenous/git-templates --dev
```

## Usage

From the project root:

```bash
vendor/bin/gitlab-templates
```

Templates are createed in `.gitlab/issue_templates/`.

### Project Directory

The target project directory is defined in this order:

1. Option `--project-dir` / `-d`
2. `composer.json` key `extra.git-templates-project-dir`
3. Environment variable `PROJECT_DIR`
4. Default : current directory

### Repository Type

The repository type is defined in this order:

1. Option `--repository-type` / `-r`
2. `composer.json` key `extra.git-templates-repository-type`
3. Environment variable `REPOSITORY_TYPE`
4. Default : `gitlab`

Available repository types: `gitlab`, `github`, `gitbucket`.

Template destination by repository type:

- `gitlab` -> `.gitlab/issue_templates`
- `github` -> `.github/ISSUE_TEMPLATE`
- `gitbucket` -> `.gitbucket/issue_templates`

**Examples**

Command line:
```bash
vendor/bin/git-templates -r github
```

Via environment variable
```bash
REPOSITORY_TYPE=gitbucket vendor/bin/git-templates
```

Via composer.json
```json
{
   "extra": {
     "git-templates-repository-type": "gitlab"
   }
}
```

### Locale

The locale is defined in this order:

1. Option `--locale` / `-l`
2. `composer.json` key `extra.git-templates-locale`
3. Environment variable `LANGUAGE`
4. Default : `en_US`

Available locales: `fr_FR`, `en_US`.

**Examples**

Command line:
```bash
vendor/bin/git-templates -l fr_FR
```

Via environment variable
```bash
LANGUAGE=fr_FR vendor/bin/git-templates
```

Via composer.json
```json
{
   "extra": {
     "git-templates-locale": "fr_FR"
   }
}
```

### Options

| Option | Alias | Description |
|---|---|---|
| `--project-dir` | `-d` | Root path of the project (default : `extra.git-templates-project-dir`, then `PROJECT_DIR`, then current directory) |
| `--repository-type` | `-r` | Repository type to use (default : `extra.git-templates-repository-type`, then `REPOSITORY_TYPE`, then `gitlab`) |
| `--locale` | `-l` | Locale to use (default : `extra.git-templates-locale`, then `LANGUAGE`, then `en_US`) |

## Licence

[GPL-3.0](LICENSE)
