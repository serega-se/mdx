<?php

namespace Se\Mdx\Structure;

class WithExpression extends Expression
{
    private string $alias;
    private Expression $expression;

    public function __toString(): string
    {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     */
    public function setAlias(string $alias): self
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return Expression
     */
    public function getExpression(): Expression
    {
        return $this->expression;
    }

    /**
     * @param Expression $expression
     */
    public function setExpression(Expression $expression): self
    {
        $this->expression = $expression;
        return $this;
    }


}
