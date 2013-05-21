# Proxy Targets Configuration
The used target can be configured to be switched according to IP-ranges and URL patterns of client's request.


## Configuration File
Proxy targets are configured in local/config/vufind/TargetsProxy.ini


## Configuration Parameters
The proxy definition is defined in the section [TargetsProxy], with the following parameters:

    [TargetsProxy]
	tabkey			The key of the tab where target-switching is applied
	targetKeys		Comma-separated list of keys of configured targets

For each of the keys listed in targetKeys there must be a section of that name, defining match-patterns for detecting
that target from IP range and/or URL:

	[Example_Target]
	patterns_ip		Comma-separated IP address patterns, see section "IP pattern types" for examples
	patterns_url	Comma-separated strings of which one must equal to- / be contained in- the hostname
	searchClassId	Search target to switch to, i.e. 'Solr' or 'Summon'

### IP pattern types
The following types of IP match patterns are supported:

*   Single,		ex: 127.0.0.1
*   Wildcard,	ex: 172.0.0.*	or	173.0.*.*	etc.
*   Mask,		ex: 126.1.0.0/255.255.0.0
*   Section,	ex: 125.0.0.1-125.0.0.9
