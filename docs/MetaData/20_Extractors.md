# Extractors
This bundle comes with one pre-installed extractor: the `IntegratorExtractor`. 
This service will extract all data from your enabled integrators.

Every extractor has two methods:

### supports($element)
This method passes a various element (document/object). 
The extractor is able to decide if this element is valid for processing.

### updateMetaData($element, $locale, $seoMetadata)
This method allows you to update the `SeoMetaData` object

## SeoMetaData Class

| Name                                     | Description                                                                                                     |
|------------------------------------------|-----------------------------------------------------------------------------------------------------------------|
| `setMetaDescription`                     | Set meta description.                                                                                           |
| `setTitle`                               | Set title.                                                                                                      |``
| `setTitle`                               | Set title.                                                                                                      |
| `addExtraProperty`, `setExtraProperties` | Add a extra property.                                                                                           |
| `addSchema`                              | Add a schema block ( `application/ld+json`). **Note!** No script tags are allowed here, only encoded json data! |
| `addExtraName`, `setExtraNames`          | Add a extra name meta field.                                                                                    |
| `addExtraHttp`, `setExtraHttp`           | Add a extra http meta field.                                                                                    |

> **Note:** Extractors are prioritized services. 
> If another extractor supports your element too it could override your current definition.

## More Information
- [Custom Extractor](./Extractor/10_CustomExtractor.md)