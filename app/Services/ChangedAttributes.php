<?php

namespace App\Services;

class ChangedAttributes
{
    public static function getChangedAttributes($record)
    {
        // determine which attributes have changed
        $ChangedAttributes = $record->getDirty();

        // store old and new values before saving (because after executing $record->save() the old values are destroyed )
        $changedValues = [];

        foreach ($ChangedAttributes as $attribute => $newValue) {
            if ($record->getOriginal($attribute) != $newValue) {
                $changedValues[$attribute] = [
                    'old' => $record->getOriginal($attribute),
                    'new' => $newValue,
                ];
            }
        }

        return $changedValues;
    }
}
