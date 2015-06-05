[![Build Status](https://travis-ci.org/horike37/Trust-Form.svg?branch=master)](https://travis-ci.org/horike37/Trust-Form)
# Trust-Form
=== Trust Form ===
Contributors: horike
Tags:  form, contact form, contact
Requires at least: 3.3
Tested up to: 3.8.3
Stable tag: 1.8.8

Trust Form is a contact form with confirmation screen and mail and data base support.

== Description ==

Trust Form is a contact form with confirmation screen and mail and data base support.

= three features: =
* You can create forms without knowledge of HTML. Because Trust Form is a user-friendly interface.
* Form transition is input to confirmation to completion of three steps.
* More than one administrator can manage Posts.

= Translators =
* Japanese(ja) - [Horike Takahiro](http://twitter.com/horike37)

You can send your own language pack to me.

Please contact to me.

* @[horike37](http://twitter.com/horike37) on twitter
* [Horike Takahiro](https://www.facebook.com/horike.takahiro) on facebook

= Contributors =
* [Horike Takahiro](http://twitter.com/horike37)
* [Seguchi Rie](http://5dg.biz) making the admin screen icon
* webnist Bug fixes
* [Aihara Chieko](http://webourgeon.com/) making the default css
* [natasha](http://natasha.jp/) making admin UI 
* [Caxias Hosoya](https://wordpress.org/plugins/caxias-hosoya/) fixed bug

== Installation ==

1. Upload `trust-form` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Screenshots ==

1. Add form screen.
2. Creating a form.
3. Insert copy and paste the short-code into page or post.
4. Show contact form.
5. Post management.
6. Post management detail.

== Changelog ==
= 1.0-alpha =
* alpha version first release. 
= 1.0.1-alpha =
* Bug fixes and admin mail add [from name]. 
= 1.0.1 =
* Add Auto reply mail
* Add CSV Download
* Add HTML templete
= 1.1 =
* Add Akismet spam filtering
= 1.3.8 =
* When you write define( 'TRUST_FORM_DB_SUPPORT', false ); in wp-config.php, don't store in DB.
= 1.4.0 =
* Add form duplicate
= 1.5.0 =
* Set label for checkbox and radio.
* Changed template system.
* add an export capability.
* Fixed a bug that does not work with PHP5.4.
* Changed the way to hold answer data.
* notice error fixed on confirm screen.
* Fixed a bug that unable to re-edit the input,confirm,finish Screen HTML dialog box.
* Fixed a bug that a contact is sent in double.
= 1.5.1 =
* Add payola link.
= 1.5.2 =
* register active hook does not fired to when update.. fixed.
= 1.5.3 =
* Created a bulk action for posts.
= 1.5.4 =
* Fixed a bug in the data disappears upgrade from 1.4.2.
= 1.5.5 =
* Fixed a bug update 1.5.4
= 1.5.6 =
* Fixed a bug for duplicate form.
= 1.5.7 =
* Fixed a bug for admin screen.
= 1.6.0 =
* Remove the function of CSS EDITOR. But you can restore this function when you write define( 'TRUST_FORM_DEFAULT_STYLE', false ); in wp-config.php,
* Add default css by making [Aihara Chieko](http://webourgeon.com/)
= 1.7.0 =
* Write `[title]` of form element on From Name, From, mail body. Then replace submit content from the form.
= 1.7.1 =
* Add filter hook for validate message.
= 1.8.0 =
* Default css corresponding to responsive by making [Aihara Chieko](http://webourgeon.com/)
* Add e-mail re-entering.
* Add other setting.
= 1.8.1 =
* Fixd a bug that warning error on finish screen.
= 1.8.3 =
* Add filter.
= 1.8.6 =
* Add Custom hooks.
= 1.8.7 =
* Delete CSV directory.
= 1.8.8 =
* some fix
= 2.0 =
* Update admin UI by making [natasha](http://natasha.jp/)
