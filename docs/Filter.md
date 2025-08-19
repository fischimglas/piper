# Filter

Status: Complete

Filters are applied after the data has been processed by the adapter. This is because most of the adapters will deliver
raw data which is mostly too verbose. (E.g. Google Search, OpenAI JSON, etc.). With the filters, you can transform the
data as per your needs. A set of filters are included, but you might need to create your own for specific use cases.
Filters are always chained and consumed by "reducing" the input (Which means that a filter following another filter will
receive the output of the first filter as input). Sequences can contain only filters.
