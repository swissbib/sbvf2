# TargetProxy Configuration
Some configuration options can be configured to change according to the detected
IP-range of the caller or to the hostname in the URL of the client's request.
For the moment, this is implemented for the Summon API.

## Configuration File

### local/config/vufind/config.ini
For the Summon API, you must supply the default values in a section
called [Summon]. After that, you can define additional sections with
special values. The sections may be named arbitrarily. We suggest
using the prefix *Summon_*. Example:

    [Summon]
    apiId   = default
    apiKey  = XYZ123456789ABC

    [Summon_Basel]
    apiId   = unibas
    apiKey  = 123456789ABCdEf

    [Summon_Bern]
    apiId   = unibe
    apiKey  = 098765XYZaBcDeF

### local/config/vufind/TargetsProxy.ini
Here you define the IP ranges or hostname/URL patterns, for which a special
section of config.ini should be used.

To be active, the section names must be listed in a comma separated list in
the appropriate key under the [TargetsProxy] section.

    ; "Targets Proxy" configuration
    ; depending on the detected IP range or URL hostname of the request
    ; defined here, the corresponding section in config.ini will be used.

    [TargetsProxy]
    targetKeysSummon = Summon_Basel,Summon_Bern

    [Summon_Basel]
    patterns_ip  = 131.152.*.*,145.250.210.*
    petterns_url = basel.swissbib.ch

    [Summon_Bern]
    patterns_ip  = 130.92.*.*
    patterns_url = bern.swissbib.ch,testbern.swissbib.ch

    [TrustedProxy]
    loadbalancer = 131.152.226.251,131.152.226.241,131.152.226.242

### IP patterns
A comma separated list of IP match patterns. The following types patterns are supported:

*   Single,		ex: 127.0.0.1
*   Wildcard,	ex: 172.0.0.*	or	173.0.*.*	etc.
*   Mask,		ex: 126.1.0.0/255.255.0.0
*   Section,	ex: 125.0.0.1-125.0.0.9

### URL patterns
A comma separated list of virtual hostnames in the caller's URL.

### TrustedProxy
A comma separated list of IPs that can be trusted as proxy. Here, the IP of a load balancer
can be added. Zend frameworks tests against this list,
see /zendframework/library/Zend/Http/PhpEnvironment/RemoteAddress.php->getIpAddressFromProxy()

### Match condition logic
There are two conditions, patterns_ip and patterns_url. Both are optional, one is
required for the detection. If both are given, then the patterns_url takes precedence
over pattern_ip.

In the above example: calling http://testbern.swissbib.ch will activate the [Summon_Bern]
configuration, even if the caller's IP is within the [Summon_Basel] range. This feature
is meant primarily for testing purposes.

## Implementation
### Swissbib\TargetsProxy

* **TargetsProxy.php**: extracts the appropriate values from the configuration files, according to the user's IP and/or URL
* **IpMatcher.php**: matches current IP against proxy target IP ranges
* **UrlMatcher.php**: matches current request hostname against proxy target hostnames

### Swissbib\VuFind\Search\Factory

* **SummonBackendFactory.php**: overrides default credentials with values from target proxy

## How to expand functionality?

So far proxy handling is only implemented for Summons.
To implement target switching for e.g. Solr

0. define a new key targetKeysSolr in the [TargetsProxy] section of TargetsProxy.ini
1. add SolrBackendFactory into Swissbib module
2. add dependency injection of the factory in module.config.php (analogous to summon)
