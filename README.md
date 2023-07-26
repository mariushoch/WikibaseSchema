# WikibaseSchema

## Make Mobile Termbox work

In your termbox directories (e.g. `extensions/Wikibase/view/lib/wikibase-termbox`),
find the following regex `^(Q|P)[1-9]\\d{0,9}$` and adjust it to `^(Q|P|S)[1-9]\\d{0,9}$`.

In Wikibase `repo/includes/RepoHooks.php` in the method `onBeforePageDisplayMobile` adjust the expression for
`$isEntityTypeWithTermbox` to include ` || $entityType === 'schema'`;

If you now switch to mobile view (and the mobile Termbox works on Items and Properties for you),
then it should now also work on WikibaseSchema Schemas.

## Changes needed in other components

This needs [WikibaseSerializationJavaScript pull #79](https://github.com/wmde/WikibaseSerializationJavaScript/pull/79) for the UI to fully function.