<?php

namespace Se\Mdx\Structure;

class MemberExpression extends Expression
{
    private string $expression;

    /**
     * @param string $expression
     */
    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getExpression();
    }

    /**
     * @return string
     */
    public function getExpression(): string
    {
        return $this->expression;
    }

    /**
     * @param string $expression
     * @return MemberExpression
     */
    public function setExpression(string $expression): self
    {
        $this->expression = $expression;
        return $this;
    }
}
