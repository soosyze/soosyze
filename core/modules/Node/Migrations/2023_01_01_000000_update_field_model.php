<?php

use Soosyze\Core\Modules\Node\Model\Field\SelectOption;
use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        $nodeTypes = $req
            ->from('node_type_field')
            ->leftJoin('field', 'field_id', '=', 'field.field_id')
            ->where('field_type', '=', 'select')
            ->fetchAll();

        /** @phpstan-var array{ field_option: string, field_id: int, node_type: string } $type */
        foreach ($nodeTypes as $type) {
            /** @phpstan-var array<int, string> $options */
            $options = (array) json_decode($type['field_option'], true);

            $selectOption = SelectOption::create();
            foreach ($options as $key => $label) {
                $selectOption->addOption($label, $key);
            }

            $req->update('node_type_field', ['field_option' => json_encode($selectOption)])
                ->where('field_id', '=', $type['field_id'])
                ->where('node_type', '=', $type['node_type'])
                ->execute();
        }
    }
};
