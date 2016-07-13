# Todo

- Review caching strategy, including tags and contexts.

# Tests

- Additional test coverage
- Improve test performance
- Implement more PHPUnit and KernelTestBase tests
- Missing Tests
    - \Drupal\yamlform\Controller\YamlFormOptionsController::autocomplete
    - src/Plugin/Field
        - \Drupal\yamlform\Plugin\Field\FieldFormatter\YamlFormEntityReferenceEntityFormatter
        - \Drupal\yamlform\Plugin\Field\FieldType\YamlFormEntityReferenceItem
        - \Drupal\yamlform\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget
    
# Questions

- Should submitted values be casted to datatypes?  
  For example, should number elements be casted to integers.

- Should we support private file uploads?  

- How should handlers deal with their global settings?

- Is the YamlForm entity doing too much? 
