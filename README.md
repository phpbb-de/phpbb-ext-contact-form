# phpBB 3.1 Extension - phpBB.de Contact Form

## Installation

Clone into ext/phpbbde/contactform:

    git clone https://github.com/phpbb-de/phpbb-ext-contact-form ext/phpbbde/contactform

Go to ext/phpbbde/contactform and install dependencies:

	php composer.phar install --no-dev

If you wish to contribute to development, you should also consider installing the development dependencies by leaving out --no-dev.
	
Go to "ACP" > "Customise" > "Extensions" and enable the "phpBB.de Contact Form" extension.

## Development

If you find a bug, please report it on https://github.com/phpbb-de/phpbb-ext-contact-form

## Automated Testing

We use automated unit tests including functional tests to prevent regressions. Check out our travis build below:

master: [![Build Status](https://travis-ci.org/phpbb-de/phpbb-ext-contact-form.png?branch=master)](http://travis-ci.org/phpbb-de/phpbb-ext-contact-form)

## License

[GPLv2](license.txt)
