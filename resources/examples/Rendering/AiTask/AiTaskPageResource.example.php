<?php

declare(strict_types=1);

namespace App\Application\Resource\Page;

final class AiTaskPageResource
{
    public string $id = '';
    public string $status = '';
    public ?string $result = null;

    public function fromTask(object $task): self
    {
        $id = $task->id ?? '';
        $status = $task->status ?? 'pending';
        $result = $task->result ?? null;

        $this->id = is_scalar($id) ? (string) $id : '';
        $this->status = is_scalar($status) ? (string) $status : 'pending';
        $this->result = is_scalar($result) ? (string) $result : null;

        return $this;
    }
}
