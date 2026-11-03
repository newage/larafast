<?php

namespace App\Faq;

use RuntimeException;

/** Pinecone or Claude (answer step) failed: HTTP 502. */
class UpstreamFailure extends RuntimeException {}
