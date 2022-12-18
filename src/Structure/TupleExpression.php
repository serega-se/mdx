<?php
namespace Se\Mdx\Structure;

class TupleExpression extends Expression
{
    private array $expressions;

    public function __toString(): string
    {
        return sprintf("(%s)", implode(', ', $this->expressions));
    }

    /**
     * @return array
     */
    public function getExpressions(): array
    {
        return $this->expressions;
    }

    /**
     * @param Expression $expression
     * @return TupleExpression
     */
    public function addExpression(Expression $expression): self
    {
        $this->expressions[] = $expression;
        return $this;
    }


}
