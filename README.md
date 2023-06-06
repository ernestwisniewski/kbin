# Kbin

[![Maintainability](https://api.codeclimate.com/v1/badges/ee285c05da04524ea2f9/maintainability)](https://codeclimate.com/github/ernestwisniewski/kbin/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/ee285c05da04524ea2f9/test_coverage)](https://codeclimate.com/github/ernestwisniewski/kbin/test_coverage)

Kbin is a modular, decentralized content aggregator and microblogging platform running on the Fediverse network. It can
communicate with many other ActivityPub services, including Mastodon, Lemmy, Pleroma, Peertube. The initiative aims to
promote a free and open internet.

This is a very early beta version, and a lot of features are currently broken or in active development, such as
federation.

![](docs/images/kbin.png)

* [https://kbin.pub](https://kbin.pub) - project website
* [https://karab.in](https://karab.in) - polish-lang instance
* [https://dev.karab.in](https://dev.karab.in) - instance for testing purposes only (develop branch / might be temporarily unavailable)

---

### Apps

* [kbin-mobile](https://codeberg.org/Kbin/kbin-mobile) (Flutter / Dart) (currently transferred)

### Libraries

* [kbin-js-client](https://codeberg.org/Kbin/kbin-js-client) (TypeScript) (currently transferred)
* [kbin-dart-client](https://codeberg.org/Kbin/kbin-dart-client) (Dart) (currently transferred)

## Getting Started

### Requirements

[https://symfony.com/doc/6.1/reference/requirements.html](https://symfony.com/doc/6.1/reference/requirements.html)

* PHP version: 8.1 or higher
* GD or Imagemagick php extension
* NGINX / Apache / Caddy
* PostgreSQL
* Redis (optional)
* Mercure (optional)
* RabbitMQ (optional)

## Documentation

* [User Guide](https://codeberg.org/Kbin/kbin-core/wiki#user-guide)
* [Admin Guide](https://codeberg.org/Kbin/kbin-core/wiki#admin-guide)
* [Kbin REST API Reference](https://docs.kbin.pub)
* [Kbin ActivityPub Reference](https://fedidevs.org/projects/kbin/)
* Kbin GraphQL Reference

## Federation

### Official Documents

* [ActivityPub standard](https://www.w3.org/TR/activitypub/)
* [ActivityPub vocabulary](https://www.w3.org/TR/activitystreams-vocabulary/)

### Unofficial Sources

* [A highly opinionated guide to learning about ActivityPub](https://tinysubversions.com/notes/reading-activitypub/)
* [ActivityPub as it has been understood](https://flak.tedunangst.com/post/ActivityPub-as-it-has-been-understood)
* [Schema Generator 3: A Step Towards Redecentralizing the Web!](https://dunglas.fr/2021/01/schema-generator-3-a-step-towards-redecentralizing-the-web/)
* [API Platform ActivityPub](https://github.com/api-platform/activity-pub)

## Languages

* English
* Polish
* Dutch ([Vistaus](https://github.com/Vistaus))

## Credits

* [grumpyDev](https://karab.in/u/grumpyDev): Logotype, icons, kbin-theme 

## Support us

###

[<img src="docs/images/partners/entrust.png" alt="NGI Zero Entrust">](https://nlnet.nl/project/Kbin/)

###

[<img src="docs/images/partners/browserstack.png" alt="BrowserStack">](https://www.browserstack.com/open-source)

###

[<img src="docs/images/partners/blackfire-io.png" alt="blackfire.io">](https://www.blackfire.io)

###

[<img src="docs/images/partners/jb_beam.png" alt="JetBrains">](https://jb.gg/OpenSourceSupport)

## Contributing

* [Official repository](https://codeberg.org/Kbin/kbin-core)
* [Translations](https://translate.codeberg.org/projects/kbin/) (currently transferred)

## License

[AGPL-3.0 license](https://github.com/ernestwisniewski/kbin/blob/main/LICENSE)
