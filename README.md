# Symfony HTTP Cache Bug Reproducer: `Accept-Language` in Vary Header

This repository reproduces a subtle caching issue in Symfony's HTTP cache system when the `Accept-Language` header is present in the `Vary` response headers but **not placed at the end** of the array.

## The Bug

When Symfony's response includes a `Vary` header like:

```http
Vary: Foo, Accept-Language
```

The HTTP cache behaves correctly.

However, if the Vary header is ordered like:

```http
Vary: Accept-Language, Foo
```

Then caching may fail to differentiate properly between requests, leading to incorrect cache hits.

Symfony includes (int this [PR](https://github.com/symfony/symfony/pull/61368)) a safeguard in `CacheAttributeListener` to always push `Accept-Language` to the end of the `Vary` header. This demo shows how removing that safeguard causes incorrect caching behavior.

## Steps to Reproduce

1. Make the first request:
```bash
curl -X GET 'http://localhost:8000/issue/cache/attribute' \
     --header 'foo: 12212' \
     --header 'accept-language: es'
```
Example response:
```text
12
```
2. Make a second request (same Accept-Language, different foo):

```bash
curl -X GET 'http://localhost:8000/issue/cache/attribute' \
     --header 'foo: 121' \
     --header 'accept-language: es'
```

Example response:
```text
88
```

Repeat the first request again:
```bash
curl -X GET 'http://localhost:8000/issue/cache/attribute' \
     --header 'foo: 12212' \
     --header 'accept-language: es'
```
Expected response (the same of the first request):
```text
12
```
**If Accept-Language is last in the Vary header, this works correctly.**

## Simulating the Bug

To reproduce the issue, go to `CacheAttributeListener.php`

Comment out the following code block:

```php
if (($vary = $response->getVary()) && \in_array('Accept-Language', $vary, true)) {
    // Ensure 'Accept-Language' is included at the end of the Vary header
    $vary = array_filter($vary, static fn (string $value) => 'Accept-Language' !== $value);
    $response->setVary(array_merge($vary, ['Accept-Language']), true);
}
```

This allows Accept-Language to appear earlier in the Vary array.

## Repeat the Test
Run the same three curl requests again.

### Now observe:
* The `third` request, which should return 12 (the same number of the first request), may return any other number instead.


**PS. The test was make with the Symfony cli**