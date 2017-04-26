# Mixtape

Easy Models and REST API for WordPress

### Mixtape tries to be

- Fluid
- Testable
- Extendable
- No Conflicts

Mixtape
(loads classes and creates an Environment subclass instance from
 the specified lib location, with the specified prefix)

Environment
(holds globals such as data_stores, rest bundles, model factories etc)

    (a registry instance. One per environment)
    Map<String, Mixtape_Interfaces_DataStore> data_stores
    Map<String, Mixtape_Rest_Api_Bundle> rest_api_bundles
    Array<String, Mixtape_Interfaces_Model>