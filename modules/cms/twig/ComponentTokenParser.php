<?php namespace Cms\Twig;

use Twig\Node\Node as TwigNode;
use Twig\Token as TwigToken;
use Twig\TokenParser\AbstractTokenParser as TwigTokenParser;
use Twig\Error\SyntaxError as TwigErrorSyntax;

/**
 * Parser for the `{% component %}` Twig tag.
 *
 *     {% component "pluginComponent" %}
 *
 * @package october\cms
 * @author Alexey Bobkov, Samuel Georges
 */
class ComponentTokenParser extends TwigTokenParser
{
    /**
     * Parses a token and returns a node.
     *
     * @param TwigToken $token A TwigToken instance
     * @return TwigNode A TwigNode instance
     */
    public function parse(TwigToken $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        $name = $this->parser->getExpressionParser()->parseExpression();
        $paramNames = [];
        $nodes = [$name];

        $end = false;
        while (!$end) {
            $current = $stream->next();

            switch ($current->getType()) {
                case TwigToken::NAME_TYPE:
                    $paramNames[] = $current->getValue();
                    $stream->expect(TwigToken::OPERATOR_TYPE, '=');
                    $nodes[] = $this->parser->getExpressionParser()->parseExpression();
                    break;

                case TwigToken::BLOCK_END_TYPE:
                    $end = true;
                    break;

                default:
                    throw new TwigErrorSyntax(
                        sprintf('Invalid syntax in the component tag. Line %s', $lineno),
                        $stream->getCurrent()->getLine(),
                        $stream->getSourceContext()
                    );
                    break;
            }
        }

        return new ComponentNode(new TwigNode($nodes), $paramNames, $token->getLine(), $this->getTag());
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'component';
    }
}
