<?php

namespace Se\Mdx\Structure;

use function SebastianBergmann\Type\TestFixture\three;

class Query
{
    private WithExpressionList $with;
    private Expression $columns;
    private Expression $rows;
    private Query|string $from;

    public function __construct()
    {
        $this->with = new WithExpressionList();
        $this->from = "";
        $this->columns = new NullExpression();
        $this->rows = new NullExpression();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $result = [];
        if (!empty($this->getWith())) {
            $result[] = sprintf("WITH %s", implode(', ', $this->getWith()));
        }

        $expressions = [];
        if (!$this->getColumns() instanceof NullExpression) {
            $expressions[] = sprintf("%s ON COLUMNS", $this->getColumns());
        }
        if (!$this->getRows() instanceof NullExpression) {
            $expressions[] = sprintf("%s ON ROWS", $this->getRows());
        }
        $result[] = sprintf("SELECT %s", implode(', ', $expressions));

        if ($this->getFrom() instanceof Query) {
            $result[] = sprintf(" FROM ( %s )", $this->getFrom());
        } else {
            $result[] = sprintf("FROM %s", $this->getFrom());
        }

        return implode(" ", $result);
    }

    /**
     * @return array
     */
    public function getWith(): array
    {
        $result = [];
        /** @var WithExpression $expression */
        foreach ($this->with->getExpressions() as $expression) {
            $result[] = sprintf("SET %s AS %s", $expression->getAlias(), $expression->getExpression());
        }
        return $result;
    }

    /**
     * @param WithExpressionList $with
     * @return Query
     */
    public function setWith(WithExpressionList $with): self
    {
        $this->with = $with;
        return $this;
    }

    /**
     * @return Expression
     */
    public function getRows(): Expression
    {
        return $this->rows;
    }

    /**
     * @param Expression $rows
     * @return Query
     */
    public function setRows(Expression $rows): self
    {
        $this->rows = $rows;
        return $this;
    }

    /**
     * @return Expression
     */
    public function getColumns(): Expression
    {
        return $this->columns;
    }

    /**
     * @param Expression $columns
     * @return Query
     */
    public function setColumns(Expression $columns): self
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * @return Query|string
     */
    public function getFrom(): Query|string
    {
        return $this->from;
    }

    /**
     * @param Query|string $from
     */
    public function setFrom(Query|string $from): self
    {
        $this->from = $from;
        return $this;
    }

}
