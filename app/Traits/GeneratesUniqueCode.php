<?php

namespace App\Traits;

use App\Models\Transaction;
use Illuminate\Support\Str;

trait GeneratesUniqueCode
{
    /**
     * Generate a unique code.
     *
     * @return string
     */
    public function generateUniqueCode()
    {
        do {
            $code = $this->generateRandomCode();
        } while ($this->codeExists($code));

        return $code;
    }

    /**
     * Generate a random code.
     *
     * @return string
     */
    private function generateRandomCode()
    {
        return Str::random(6);
    }

    /**
     * Check if the code exists in the Transaction model.
     *
     * @param string $code
     * @return bool
     */
    private function codeExists($code)
    {
        return Transaction::where('reff_id', $code)->exists();
    }
}