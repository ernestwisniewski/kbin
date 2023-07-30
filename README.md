# Kbin

[![Maintainability](https://api.codeclimate.com/v1/badges/ee285c05da04524ea2f9/maintainability)](https://codeclimate.com/github/ernestwisniewski/kbin/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/ee285c05da04524ea2f9/test_coverage)](https://codeclimate.com/github/ernestwisniewski/kbin/test_coverage)

/kbin is a modular, decentralized content aggregator and microblogging platform running on the Fediverse network. It can
communicate with many other ActivityPub services, including Mastodon, Lemmy, Pleroma, Peertube. The initiative aims to
promote a free and open internet.

The inspiration came from platforms like [Postmill](https://postmill.xyz/), [Strimoid](https://github.com/Strimoid/Strimoid), and [Pixelfed](https://pixelfed.org/).

> _Note_:
> This is a very early beta version, and a lot of features are currently broken or in active development, such as
> federation.

![Kbin logo](docs/images/kbin.png)

- [https://kbin.pub](https://kbin.pub) - project website
- [https://kbin.social](https://kbin.social) - main instance
- [List of instances](https://fedidb.org/software/kbin)

## Contributing

- [Official repository on Codeberg](https://codeberg.org/Kbin/kbin-core)
- [Translations](https://translate.codeberg.org/projects/kbin/)
- [#kbin-space:matrix.org](https://matrix.to/#/#kbin-space:matrix.org)
- [Contribution guidelines](https://codeberg.org/Kbin/kbin-core/wiki/Contributing) - please read first, including before opening an issue!

---

## Getting Started

### Requirements

[See also Symfony requirements](https://symfony.com/doc/current/setup.html#technical-requirements)

- PHP version: 8.2 or higher
- GD or Imagemagick PHP extension
- NGINX / Apache / Caddy
- PostgreSQL
- Redis (optional)
- Mercure (optional)
- RabbitMQ (optional)

## Documentation

- [User Guide](https://codeberg.org/Kbin/kbin-core/wiki#user-guide)
- [Admin Guide](https://codeberg.org/Kbin/kbin-core/wiki#admin-guide)
- [Kbin REST API Reference](https://docs.kbin.pub)
- [Kbin ActivityPub Reference](https://fedidevs.org/projects/kbin/)
- Kbin GraphQL Reference

## Federation

### Official Documents

- [ActivityPub standard](https://www.w3.org/TR/activitypub/)
- [ActivityPub vocabulary](https://www.w3.org/TR/activitystreams-vocabulary/)
- [Activity Streams](https://www.w3.org/TR/activitystreams-core/)

### Unofficial Sources

- [A highly opinionated guide to learning about ActivityPub](https://tinysubversions.com/notes/reading-activitypub/)
- [ActivityPub as it has been understood](https://flak.tedunangst.com/post/ActivityPub-as-it-has-been-understood)
- [Schema Generator 3: A Step Towards Redecentralizing the Web!](https://dunglas.fr/2021/01/schema-generator-3-a-step-towards-redecentralizing-the-web/)
- [API Platform ActivityPub](https://github.com/api-platform/activity-pub)

## Languages

- English
- Polish
- Dutch ([Vistaus](https://github.com/Vistaus), [Melroy](https://github.com/melroy89))
- Japanese ([@dannekrose@brioco.social](https://brioco.social/@dannekrose))

## Credits

- [grumpyDev](https://karab.in/u/grumpyDev): Logotype, icons, kbin-theme

## Donate

- [LiberaPay](https://liberapay.com/kbin)
- [Patreon](https://www.patreon.com/kbin_pub)
- [Buy me a coffee](https://www.buymeacoffee.com/kbin)

_Note:_ Please, also don't forget about all the [contributors](https://codeberg.org/Kbin/kbin-core/activity/monthly).
These are people who are actively contributing to /kbin project and are all volunteers.

## Support us

###

[<img src="docs/images/partners/entrust.png" alt="NGI Zero Entrust" height="75">](https://nlnet.nl/project/Kbin/)

###

[<img src="docs/images/partners/browserstack.png" alt="BrowserStack" height="75">](https://www.browserstack.com/open-source)

###

[<img src="docs/images/partners/blackfire-io.png" alt="blackfire.io" height="75">](https://www.blackfire.io)

###

[<img src="docs/images/partners/jb_beam.png" alt="JetBrains" height="150">](https://jb.gg/OpenSourceSupport)

## License

[AGPL-3.0 license](https://github.com/ernestwisniewski/kbin/blob/main/LICENSE)
