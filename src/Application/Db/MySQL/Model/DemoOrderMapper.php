<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Model;

use Semitexa\Orm\Attribute\AsMapper;
use Semitexa\Orm\Contract\TableModelMapper;

#[AsMapper(tableModel: DemoOrderTableModel::class, domainModel: DemoOrderResource::class)]
final class DemoOrderMapper implements TableModelMapper
{
    public function toDomain(object $tableModel): object
    {
        $tableModel instanceof DemoOrderTableModel || throw new \InvalidArgumentException('Unexpected table model.');
        $resource = new DemoOrderResource();
        foreach (get_object_vars($tableModel) as $property => $value) {
            $resource->{$property} = $value;
        }
        return $resource;
    }

    public function toTableModel(object $domainModel): object
    {
        $domainModel instanceof DemoOrderResource || throw new \InvalidArgumentException('Unexpected resource model.');
        return new DemoOrderTableModel(...get_object_vars($domainModel));
    }
}
