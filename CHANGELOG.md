# Changelog

All notable changes to this package are documented here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the package follows [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Added

- `lang:check` reports translation keys used in the source code that are missing from a language file.
- `lang:unused` reports keys in the language files that the source code does not use and removes them with `--prune`.
- `lang:format` rewrites language files with sorted keys in a consistent style and verifies them with `--check`.
