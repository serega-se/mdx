<?php

namespace Se\Mdx\Structure;

class NullExpression  extends Expression
{
    /**
     * @return string
     */
    public function __toString(): string
    {
        return "";
    }
}
