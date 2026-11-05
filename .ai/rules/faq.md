---
paths:
  - 'app/Faq/**'
---

# Faq

## Chat feature structure
Chat/FAQ logic goes in plain classes under `app/Faq/`. Use constructor injection with `private readonly` promoted properties, and wire the graph in `FaqServiceProvider`. Don't add Action, Service or Repository layers. Put external APIs behind an interface, as `ClaudeGateway` does. Keep controllers thin and invokable. They translate domain exceptions (`InvalidChatRequest`, `UpstreamFailure`) into JSON responses.
