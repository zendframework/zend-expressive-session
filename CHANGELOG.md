# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.1.0 - TBD

### Added

- [#27](https://github.com/zendframework/zend-expressive-session/pull/27) adds a new interface, `Zend\Expressive\Session\SessionIdentifierAwareInterface`.
  `SessionInterface` implementations should also implement this interface, and
  persistence implementations should only create and consume session
  implementations that implement it. The interface defines a single method,
  `getId()`, representing the identifier of a discovered session. This allows
  the identifier to be associated with its session data, ensuring that when
  concurrent requests are made, persistence operates on the correct identifier.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.1 - TBD

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.0 - 2018-03-15

### Added

- [#18](https://github.com/zendframework/zend-expressive-session/pull/18) adds
  support for PSR-15 middleware.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- [#14](https://github.com/zendframework/zend-expressive-session/pull/14) and
  [#18](https://github.com/zendframework/zend-expressive-session/pull/18) remove
  support for http-interop/http-middleware and http-interop/http-server-middleware.

- [#5](https://github.com/zendframework/zend-expressive-session/pull/5) removes
  the method `LazySession::segment()`. This method was a remnant from a previous
  refactor, and not intended for the final API. Considering that `Session` does
  not implement the method, calling it would lead to a fatal error anyways.

### Fixed

- Nothing.

## 0.1.0 - 2017-10-10

Initial release.

### Added

- Everything.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
