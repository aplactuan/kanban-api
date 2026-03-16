<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait ParsesIncludes
{
    /**
     * Parse a comma-separated ?include=... query parameter against an allowed list.
     *
     * @param  string[]  $allowed
     * @return string[]
     */
    protected function parseIncludes(Request $request, array $allowed): array
    {
        $raw = (string) $request->query('include', '');

        $requested = array_filter(array_map('trim', explode(',', $raw)));

        return array_values(array_intersect($requested, $allowed));
    }
}
