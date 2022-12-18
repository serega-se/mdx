<?php

namespace Se\Mdx\Structure;

class SetExpression extends Expression
{

    private array $expressions;

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf("{%s}", implode(', ', $this->expressions));
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
     * @return SetExpression
     */
    public function addExpression(Expression $expression): self
    {
        $this->expressions[] = $expression;
        return $this;
    }
}
