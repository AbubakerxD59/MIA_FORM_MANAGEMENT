<?php

use App\Enums\UnitType;
use App\Http\Middleware\FidaUser;

if (! function_exists('unit_types')) {
    /**
     * Get all unit types for select dropdowns.
     *
     * @return array<string>
     */
    function unit_types(): array
    {
        return UnitType::values();
    }
}

if (! function_exists('is_fida_user')) {
    /**
     * Whether the current user is the restricted FIDA account.
     */
    function is_fida_user(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return hash_equals(
            strtolower(FidaUser::ALLOWED_EMAIL),
            strtolower((string) $user->email)
        );
    }
}

if (! function_exists('excel_footer_text')) {
    /**
     * Get the Excel footer text.
     *
     * @return string
     */
    function excel_footer_text(): string
    {
        // $footerText = "&CMIA CONSTRUCTION\n";
        $footerText = "&C&12&BMIA CONSTRUCTION\n";
        $footerText .= "\n";
        $footerText .= "\n&C&10 Consultant - Designer - Estimator - Contractor\n";
        $footerText .= "\n&C&10 59 - MAIN VIP EXT AECHS RAWALPINDI\n";
        $footerText .= "&C&10 - 03218600259 -";
        return $footerText;
    }
}
