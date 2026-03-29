<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Model;

use Semitexa\Orm\Attribute\AsMapper;
use Semitexa\Orm\Contract\TableModelMapper;

#[AsMapper(tableModel: DemoAnalyticsSnapshotTableModel::class, domainModel: DemoAnalyticsSnapshotResource::class)]
final class DemoAnalyticsSnapshotMapper implements TableModelMapper
{
    public function toDomain(object $tableModel): object
    {
        $tableModel instanceof DemoAnalyticsSnapshotTableModel || throw new \InvalidArgumentException('Unexpected table model.');
        $resource = new DemoAnalyticsSnapshotResource();
        foreach (get_object_vars($tableModel) as $property => $value) {
            $resource->{$property} = $value;
        }
        return $resource;
    }

    public function toTableModel(object $domainModel): object
    {
        $domainModel instanceof DemoAnalyticsSnapshotResource || throw new \InvalidArgumentException('Unexpected resource model.');
        return new DemoAnalyticsSnapshotTableModel(...get_object_vars($domainModel));
    }
}
