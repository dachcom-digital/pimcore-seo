# Upgrade Notes
![upgrade](https://user-images.githubusercontent.com/700119/31535145-3c01a264-affa-11e7-8d86-f04c33571f65.png)  

***

After every update you should check the pimcore extension manager. 
Just click the "update" button or execute the migration command to finish the bundle update.

***

#### Update from Version 1.1.0 to Version 1.1.1
- **[ENHANCEMENT]** Improve Dependency Check [#20](https://github.com/dachcom-digital/pimcore-seo/issues/20)

#### Update from Version 1.0.4 to Version 1.1.0
- **[ENHANCEMENT]** Respect user locales [#16](https://github.com/dachcom-digital/pimcore-seo/issues/16)
- **[ENHANCEMENT]** Add Permissions. New permissions will be added via migration. Please update your users accordingly [#17](https://github.com/dachcom-digital/pimcore-seo/issues/17)
- **[ENHANCEMENT]** Generate preview data based on user locale [#18](https://github.com/dachcom-digital/pimcore-seo/issues/18)

#### Update from Version 1.0.3 to Version 1.0.4
- **[BUGFIX]** Use absolute url in image tags, also don't store path in DB to prevent invalid data (https://github.com/dachcom-digital/pimcore-seo/issues/9)
- **[BUGFIX]** Lower third party extractor priority to allow simple overrides on project layer (https://github.com/dachcom-digital/pimcore-seo/issues/7)
- **[ENHANCEMENT]** Always update document legacy `title` and `description` (https://github.com/dachcom-digital/pimcore-seo/issues/12)
