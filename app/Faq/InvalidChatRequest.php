<?php

namespace App\Faq;

use RuntimeException;

/** The body is JSON but breaks the /chat contract: HTTP 422. */
class InvalidChatRequest extends RuntimeException {}
