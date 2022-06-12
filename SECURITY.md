# Security Policy

## Supported Versions

| Version of Soosyze | Supported     |
| ------------------ | ------------- |
| 2.x                | ✓ Supported   |
| <= 1.x             | ✗ Unsupported |

## Reporting a Vulnerability

To report a vulnerability, use the [Github bug report template](https://github.com/soosyze/soosyze/issues/new?labels=bug&template=bug_report.md).

Depending on the criticality of the bug, this will be prioritized:

- **Importance Critical**, will be processed and deployed as soon as possible:
  - complete loss of functionality,
  - data disclosure/corruption,
  - faille xss, csrf, injection,
  - affects a majority of users.
- **Importance High**, will be processed quickly ~1-2 weeks:
  - major break in functionality,
  - affects a large portion of users.
- **Importance Medium**, will be processed normally ~2-3 weeks:
  - break in functionality,
  - will affect some users but can be worked around.
- **Importance Low**, can wait for the next version:
  - minor loss of functionality,
  - affects a small amount of users and can be worked around in most cases.