<?php
/**
 * Configurable value cleaners and regex extractors.
 *
 * @package WooUseful_Order_Details_Copier
 */

namespace Vibe\WooUseful\OrderDetailsCopier;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class Transform {
    /**
     * Apply a saved cleaner rule to a value.
     *
     * @param string $value Value.
     * @param array<string,mixed> $rule Rule.
     * @return string
     */
    public static function clean( string $value, array $rule ): string {
        $trim = isset( $rule['trim'] ) ? (string) $rule['trim'] : 'none';
        if ( 'both' === $trim ) {
            $value = trim( $value );
        } elseif ( 'left' === $trim ) {
            $value = ltrim( $value );
        } elseif ( 'right' === $trim ) {
            $value = rtrim( $value );
        }

        $prefix = isset( $rule['remove_prefix'] ) ? (string) $rule['remove_prefix'] : '';
        if ( '' !== $prefix && 0 === strpos( $value, $prefix ) ) {
            $value = substr( $value, strlen( $prefix ) );
        }

        $pattern     = isset( $rule['regex_pattern'] ) ? trim( (string) $rule['regex_pattern'] ) : '';
        $replacement = isset( $rule['regex_replacement'] ) ? (string) $rule['regex_replacement'] : '';
        if ( '' !== $pattern && self::is_valid_pattern( $pattern ) ) {
            $replaced = preg_replace( $pattern, $replacement, $value );
            if ( null !== $replaced ) {
                $value = $replaced;
            }
        }

        return $value;
    }

    /**
     * Extract one capture from a source value.
     *
     * @param string $source Source value.
     * @param array<string,mixed> $rule Extractor rule.
     * @return string
     */
    public static function extract( string $source, array $rule ): string {
        $pattern = isset( $rule['pattern'] ) ? trim( (string) $rule['pattern'] ) : '';
        $group   = isset( $rule['capture_group'] ) ? max( 0, (int) $rule['capture_group'] ) : 1;

        if ( '' === $pattern || ! self::is_valid_pattern( $pattern ) ) {
            return '';
        }

        $matches = array();
        if ( 1 !== preg_match( $pattern, $source, $matches ) || ! isset( $matches[ $group ] ) ) {
            return '';
        }

        return self::clean(
            (string) $matches[ $group ],
            array(
                'trim'              => $rule['trim'] ?? 'both',
                'remove_prefix'     => $rule['remove_prefix'] ?? '',
                'regex_pattern'     => $rule['regex_pattern'] ?? '',
                'regex_replacement' => $rule['regex_replacement'] ?? '',
            )
        );
    }

    /**
     * Validate a complete PHP regular expression without leaking warnings.
     *
     * @param string $pattern Pattern including delimiters.
     * @return bool
     */
    public static function is_valid_pattern( string $pattern ): bool {
        if ( '' === trim( $pattern ) || strlen( $pattern ) > 1000 ) {
            return false;
        }

        set_error_handler( static function (): bool { return true; } );
        $result = preg_match( $pattern, '' );
        restore_error_handler();

        return false !== $result;
    }
}
