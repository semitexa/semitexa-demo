<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Model;

use Semitexa\Orm\Attribute\AsMapper;
use Semitexa\Orm\Contract\TableModelMapper;

#[AsMapper(tableModel: DemoAiTaskTableModel::class, domainModel: DemoAiTaskResource::class)]
final class DemoAiTaskMapper implements TableModelMapper
{
    public function toDomain(object $tableModel): object
    {
        $tableModel instanceof DemoAiTaskTableModel || throw new \InvalidArgumentException('Unexpected table model.');
        $resource = new DemoAiTaskResource();
        foreach (get_object_vars($tableModel) as $property => $value) {
            $resource->{$property} = $value;
        }
        return $resource;
    }

    public function toTableModel(object $domainModel): object
    {
        $domainModel instanceof DemoAiTaskResource || throw new \InvalidArgumentException('Unexpected resource model.');
        return new DemoAiTaskTableModel(...get_object_vars($domainModel));
    }
}
