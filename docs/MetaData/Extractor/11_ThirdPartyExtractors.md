# Third Party Extractors
This Bundle comes with some pre configured extractors.

## Complexity
These extractors will be enabled on cache build level.
If any supported bundle isn't installed, no service will be included - so no overload here!

## Supported Bundles
This is an accurate list of supported bundles!

***

### CoreShop Bundle
Note: After you have enabled the SEO Bundle, the default coreshop extractors (`coreshop.seo.extractor`) will be removed
to keep up the performance but also to prevent unnecessary code executions. 
If you also have some custom coreshop extractors, please migrate them to the seo bundle extractors first!

> Pro Tip: Hide the "Meta Title" and "Meta Description" fields in "CoreShopProduct" and "CoreShopCategory" class since you should use the SEO-Tab only!
> If you're going to delete them from your class definition (Only on new installations otherwise you'll lose your data!), you need to add these methods to an extended class first, otherwise coreshop will throw an exception!

- Bundle: https://github.com/coreshop/CoreShop
- Object Types: `CoreShopProduct`, `CoreShopCategory`
- Extractors:
  - Title & Description Extractor (`coreshop_title_description`)
  - OG Type, Title, Description and Image Extractor (`coreshop_og_tags`)
  
#### Disable Default CoreShop Extractor
```yaml
seo:
    meta_data_configuration:
        meta_data_provider:
            third_party:
                coreshop:
                    disable_default_extractors: true
```

***

### News Bundle
> Pro Tip: Hide the "metaTitle" and "metaDescription" fields in "NewsEntry" and "NewsCategory" (category SEO fields are unused fields by default) class since you should use the SEO-Tab only!
> If you're going to delete them from your entry class definition (Only on new installations otherwise you'll lose your data!), you need to add these methods to an extended class first, otherwise the news bundle will throw an exception!

- Bundle: https://github.com/dachcom-digital/pimcore-news
- Object Types: `NewsEntry`
- Extractors:
  - Entry Meta Extractor (`news_entry_meta`, still using the `NewsBundle\Generator` Service to generate metadata and keep bc)
 
#### Disable Default News Extractor
```yaml
seo:
    meta_data_configuration:
        meta_data_provider:
            third_party:
                news:
                    disable_default_extractors: true
```