<?php

namespace App\Http\patterns\builder;

use App\Models\saved_properties_settings;
use App\Models\saved_properties_settings_answers;

class SavedPropertySettingBuilder
{
    private $main_obj = null;

    private $overwire = null;

    public function build_main_setting($data)
    {
        if (isset($data['overwire'])) {
            $this->overwire = 1;
        }
        $this->main_obj = saved_properties_settings::query()->updateOrCreate([
            'id' => $data['id'] ?? null,
        ], $data);

        return $this;
    }

    public function build_answers_setting($data)
    {
        if ($this->overwire) {
            $this->delete_all();
        }
        foreach ($data as $answer) {
            $answer['saved_properties_settings_id'] = $this->main_obj->id;

            saved_properties_settings_answers::query()->updateOrCreate([
                'id' => $answer['id'] ?? null,
            ], $answer);
        }
    }

    public function get_main_setting()
    {
        return $this->main_obj;
    }

    private function delete_all()
    {
        saved_properties_settings_answers::query()->where('saved_properties_settings_id', $this->main_obj->id)->delete();
    }
}
