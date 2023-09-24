# Contributing to /kbin

We always welcome new supporters and contributors. A quick list below of possible ways to contribute to /kbin.

> *Note*:
> Understand that /kbin is a young project, and we are all volunteers. Please be nice! ❤

## Code

The code code is mainly written in PHP using the Symfony framework with Twig templating and a bit of JavaScript & CSS.

With an account on [Codeberg](https://codeberg.org) you will be able to [fork this repository](https://codeberg.org/Kbin/kbin-core) and `git clone` the repository locally if you wish. As well as [creating a new Pull Request](https://codeberg.org/Kbin/kbin-core/pulls) in Codeberg. Also be sure to avoid regression, see below for more info about the coding style as well as testing.

### Coding Style Guide

We use [php-cs-fixer](https://cs.symfony.com/) to automatically fix code style issues according to [Symfony coding standard](https://symfony.com/doc/current/contributing/code/standards.html).  
It is based on the [PHP-FIG coding standards](https://www.php-fig.org/psr/).

Install PHP-CS-Fixer first: `composer -d tools install`

Then run the following command trying to auto-fix the issues: `./tools/vendor/bin/php-cs-fixer fix`

### Tests

When fixing a bug or implementing a new feature or improvement, we expect that test code will also be included with every delivery of production code.

There are three levels of tests that we distinguish between:

- Unit Tests: test a specific unit (SUT), mock external functions/classes/database calls, etc. Unit-tests are fast, isolated and repeatable
- Integration Tests: test larger part of the code, combining multiple units together (classes, services or alike).
- Application Tests: test high-level functionality, APIs or web calls.

For more info read: [Symfony Testing guide](https://symfony.com/doc/current/testing.html).

### Fixtures

You might want to load random data to database instead of manually adding magazines, users, posts, comments etc.  
To do so, execute: `bin/console doctrine:fixtures:load --append --no-debug`

Please note, that the command may take some time and data will not be visible during the process, but only after the finish.

- Omit `--append` flag to override data currently stored in the database
- Customize inserted data by editing files inside `src/DataFixtures` directory

## Translations

Translations are done in [Weblate](https://translate.codeberg.org/projects/kbin/).

## Documentation

Documentation is stored at in the [`docs` folder](https://codeberg.org/Kbin/kbin-core/src/branch/develop/docs) within git. And periodically synced with our Codeberg wiki pages. Create a [new pull request](https://codeberg.org/Kbin/kbin-core/pulls) with changes to the documentation files.

## Community

We have a very active [Matrix community](https://matrix.to/#/#kbin-space:matrix.org). Feel free to join our community, ask questions, share your ideas or help others!

## Reporting Issues

If you observe an error or any other issue, [create an new issue](https://codeberg.org/Kbin/kbin-core/issues) in Codeberg. A couple notes about this:

- Please try to verify that your issue is not already present before you create a new issue. You can search on existing open issues.
- We actually prefer you to **not** include `[Feature Request]` or `[Bug Report]` or similar tags in the title. Instead, we'll add the labels for you.
- If you're reporting an issue that happened while you're on a specific instance, please include **the URL**.
- If the issue is related to design/UI, please also **include screenshots**.
- If you're the server admin and have access to logging, please also **include logs** when relevant.

## Reporting Security Vulnerability

Contact Ernest (`@ernest_:matrix.org`) and/or Melroy (`@melroy:melroy.org`) via Matrix, using an encrypted room.

## I Have a Question

Before you ask a question, it is sometimes a good practice to search for existing [issues](https://codeberg.org/Kbin/kbin-core/issues) that might help you.

If you still feel the need for asking a question, we recommend [joining our community on Matrix](https://matrix.to/#/%23kbin-space:matrix.org) where you can ask your questions to our community members.

