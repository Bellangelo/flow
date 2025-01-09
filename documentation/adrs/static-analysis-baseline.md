---
status: "accepted"
date: 2024-01-D9
decision-makers: @norberttech
consulted: @bellangelo @stloyd @mleczakm
informed: Discord channel #general
---

# Static Analysis Baseline

## Context and Problem Statement

After removal of Psalm and making PHPStan the main static analysis tool, 
we looked again into hardening the static analysis configuration.

We had three globally ignored errors in PHPStan configuration

```neon
identifier: argument.type
identifier: missingType.iterableValue
identifier: missingType.generics
```

All of the above were significantly reducing the value of static analysis and code quality.

## Decision Drivers

* Availability of maintainers
* Maintenance costs

## Considered Options

* No baseline file - keep everything as is
* Add errors to the baseline file

## Decision Outcome

Chosen option: "No baseline file" because maintainers have limited time
and the introduction of a baseline might add extra maintenance costs.
Instead, errors can be suppressed by annotations in the codebase 
or globally in the static analysis tool configuration.

Error suppression should be considered an edge case 
and should be used sparingly. 
Core contributors should review and approve all suppression annotations.

### Consequences

* Good, because it doesn't add extra overhead to the maintainers.
* Bad, because ignoring globally errors allows the introduction of new errors of the same type.

### Confirmation
Maintainers / Reviewers should be aware of the decision and enforce it.

## Pros and Cons of the Options

### No baseline file

This options means that we will keep the current configuration which is to ignore 
globally the errors in the static analysis tool configuration.

```neon
ignoreErrors:
  - identifier: argument.type
  - identifier: missingType.iterableValue
  - identifier: missingType.generics
```

* Good, because developers don't need to handle atomically these errors.
* Good, because it reduces the maintenance costs.
* Bad, because ignoring globally errors allows the introduction of new errors of the same type.

### Add errors to the baseline file

Errors in the baseline specify the file and the number of occurrences of the error.

Example:
```neon
message: '#^Parameter \#2 \$actualXml of static method PHPUnit\\Framework\\Assert\:\:assertXmlStringEqualsXmlString\(\) expects string, string\|null given\.$#'
identifier: argument.type
count: 3
path: src/adapter/etl-adapter-xml/tests/Flow/ETL/Adapter/XML/Tests/Integration/XMLReaderExtractorTest.php
```

* Good, because it allows to handle atomically these errors.
* Good, because it does not allow the introduction of new errors of the same type.
* Neutral, because you cannot automatically validate that no new errors have been added when the baseline is regenerated.
* Bad, because it increases the maintenance costs.

## More Information

Discussion about baseline started in this PR: [Run PHPStan for the test suite](https://github.com/flow-php/flow/pull/1329)