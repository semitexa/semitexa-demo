<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Model;

use Semitexa\Orm\Attribute\AsMapper;
use Semitexa\Orm\Contract\TableModelMapper;

#[AsMapper(tableModel: DemoJobRunTableModel::class, domainModel: DemoJobRunResource::class)]
final class DemoJobRunMapper implements TableModelMapper
{
    public function toDomain(object $tableModel): object
    {
        $tableModel instanceof DemoJobRunTableModel || throw new \InvalidArgumentException('Unexpected table model.');
        $resource = new DemoJobRunResource();
        foreach (get_object_vars($tableModel) as $property => $value) {
            $resource->{$property} = $value;
        }
        return $resource;
    }

    public function toTableModel(object $domainModel): object
    {
        $domainModel instanceof DemoJobRunResource || throw new \InvalidArgumentException('Unexpected resource model.');
        return new DemoJobRunTableModel(...get_object_vars($domainModel));
    }
}
