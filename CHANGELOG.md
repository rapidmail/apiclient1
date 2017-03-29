## [1.8.2] 2017-03-29
* Update composer.json to support PHP7


## [1.8.1] 2017-02-15
* added optional parameter for "domain" to `add_mailing()`

## [1.8.0] 2015-04-08
* added optional parameters for "ecg" and "robinson" list to add_mailing()
* placed optional parameters "charset" and "draft" together with "ecg" and "robinson" into new settings array within add_mailing()

## [1.7.1] 2015-03-10
* added option "softdelete" to parameter "recipient_missing" in recipient_new_multi(), which ignores blacklist settings

## 2011-08-15
* added module for getting newsletter archive (data for all sent mailings of recipientlist)

## 2011-07-25
* added optional parameter $draft to mailing_new() for adding mailing as draft
* added optional parameter $publiclink_validity to statistics_mailing_get() for validity of public link
* statistics_mailing_get() returns public link and its validity (default validity is 3 days)
* recipient_edit() considers data field "foreign_id"
* recipient_new() considers data field "foreign_id"
* recipient_get() considers data field "foreign_id"
* recipient_get_multi() considers data field "foreign_id"

## 2010-04-08
* added parameter triggers statistic consideration on adding/deleting recipient

## 2009-10-10
* made result handling php 5.3 compatible

## 2009-09-08
* added parameter $fields to get_recipients() for specify datafields to receive

## 2009-06-28
* changed using file_get_contents to fsockopen for improved POST usage
* added modules for set/get metadata of recipientlist
* added SSL support
* added debug mode

## 2009-02-18
* bugfix: initialize $context to avoid notice on file_get_contents

## 2009-01-18
* add_recipient: recipient_data parameter could be empty

## 2008-12-28
* initial release
