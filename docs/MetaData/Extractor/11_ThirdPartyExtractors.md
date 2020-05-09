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

- Bundle: https://github.com/coreshop/CoreShop
- Object Types: `CoreShopProduct`, `CoreShopCategory`
- Extractors:
  - Title & Description Extractor (`coreshop_title_description`)
  - OG Type, Title, Description and Image Extractor (`coreshop_og_tags`)
 
 