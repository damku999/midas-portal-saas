<?php

namespace App\Traits;

trait HelperTrait
{
    protected function getUserActivityLogJSON(object $obj): string
    {
        $dirty = $obj->getDirty();
        $activity_log = [];
        $reference_table_array = [];
        if (! empty($dirty)) {
            $check_reference_table = false;
            if (! empty($obj->activity_model)) {
                $check_reference_table = true;
                $reference_table_array = $obj->activity_model;
            }

            foreach ($dirty as $key => $value) {
                if ($check_reference_table && array_key_exists($key, $reference_table_array)) {
                    $old_value_id = $obj->getOriginal($key);
                    $new_value_id = $obj->$key;
                    $model_name = '\\App\\Models\\'.$reference_table_array[$key]['model_name'];
                    $model_field = $reference_table_array[$key]['model_field'];
                    if (! empty($old_value_id)) {
                        $model_obj = new $model_name;
                        $old_record = $model_obj::find($old_value_id);
                        $old_value = $old_record?->$model_field ?? '';
                    } else {
                        $old_value = '';
                    }
                    $model_obj1 = new $model_name;
                    $new_record = $model_obj1::find($new_value_id);
                    $new_value = $new_record?->$model_field ?? '';
                } else {
                    $old_value = $obj->getOriginal($key);
                    $new_value = $obj->$key;
                }
                $activity_log['field_data'][] = $key;
                $activity_log['old_'.$key] = $old_value;
                $activity_log['new_'.$key] = $new_value;
            }
        }

        return json_encode($activity_log);
    }

    protected function isJson(string $string = ''): bool
    {
        json_decode($string);

        return json_last_error() == JSON_ERROR_NONE;
    }

    protected function sentenceCase(string $string): string
    {
        $sentences = preg_split(
            '/([.?!]+)/',
            $string,
            -1,
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
        );
        $new_string = '';
        foreach ($sentences as $key => $sentence) {
            $new_string .= ($key & 1) == 0 ?
            ucfirst(trim($sentence)) :
            $sentence.' ';
        }

        return trim($new_string);
    }
}
