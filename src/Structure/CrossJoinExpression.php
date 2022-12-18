<?php

namespace Se\Mdx\Structure;

class CrossJoinExpression extends Expression
{
    private array $expressions;

    /**
     * @return string
     */
    public function __toString(): string
    {
        return implode(' * ', $this->expressions);
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
     * @return CrossJoinExpression
     */
    public function addExpression(Expression $expression): self
    {
        $this->expressions[] = $expression;
        return $this;
    }
}
