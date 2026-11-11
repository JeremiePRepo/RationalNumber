<?php

declare(strict_types=1);

namespace RationalNumber\Internal;

/**
 * Deprecation notices for the APIs removed in 3.0, all worded the same way.
 *
 * @internal This class is not part of the public API and may change without notice.
 */
final class Deprecation
{
    /**
     * Trigger an E_USER_DEPRECATED notice: "<subject> is deprecated since 2.11 and will be removed
     * in 3.0. <hint> See UPGRADE-3.0.md."
     *
     * @param string $subject What is deprecated (e.g. "RationalNumber::reduce()").
     * @param string $hint What to do instead (e.g. "Use $x->divideBy($this) instead.").
     */
    public static function trigger(string $subject, string $hint): void
    {
        trigger_error(
            "{$subject} is deprecated since 2.11 and will be removed in 3.0. {$hint} See UPGRADE-3.0.md.",
            E_USER_DEPRECATED
        );
    }
}
