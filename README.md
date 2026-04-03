# GitLab Templates

CLI tool to install standardized GitLab issue templates into your projects.

## Installation

```bash
composer require agencenous/gitlab-templates
```

## Usage

From your project root:

```bash
vendor/bin/gitlab-templates
```

Templates are copied into `.gitlab/issue_templates/`.

### Locale

The locale is resolved in this order:

1. `--locale` / `-l` option
2. `LANGUAGE` environment variable
3. Defaults to `en_US`

Available locales: `fr_FR`, `en_US`.

```bash
# Use French templates
vendor/bin/gitlab-templates -l fr_FR

# Or via environment variable
LANGUAGE=fr_FR vendor/bin/gitlab-templates
```

### Options

| Option | Alias | Description |
|---|---|---|
| `--project-dir` | `-d` | Target project root path (defaults to current directory) |
| `--locale` | `-l` | Locale to use (defaults to `LANGUAGE` env var, then `en_US`) |

## Templates

- **Default.md** : Structured feature request template (use cases, technical description, affected modules, risk points, estimates).

## License

[GPL-3.0](LICENSE)
