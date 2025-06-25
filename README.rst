Page specific TypoScript files for TYPO3
========================================

Hooks into TYPO3 system to load TypoScript files based on current TYPO3 page.

Why
===

There are a lot of legacy TYPO3 sites. Those often have multiple `sys_template`
records, loading TypoScript on specific pages.

This prevents tools like `fractor
<https://packagist.org/packages/a9f/typo3-fractor>`_ to migrate the TypoScript.

Also many developers consider it best practice to not maintain TypoScript or Page
TSconfig within the database. Instead it should be in the file system, allowing
version control and deployment.
