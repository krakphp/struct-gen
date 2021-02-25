<?php

namespace Krak\StructGen\Internal;

use Krak\Lex\MatchedToken;
use Krak\Lex\TokenStream;
use function Krak\Lex\lexer;
use function Krak\Lex\skipLexer;
use function Krak\Lex\tokenStreamLexer;

/**
 * CBNF
 *
 * Start: UnionType
 * UnionType: AtomicType MaybeAtomicType
 * MaybeUnionAtomicType: pipe UnionType | e
 * AtomicType: Nullable Type  MaybeBrackets
 * Type: type MaybeGenericConstraints | Callable
 * Nullable: qmark | e
 * MaybeGenericConstraints: lt TypeList gt | e
 * TypeList: UnionType MaybeTypeList
 * MaybeTypeList: comma TypeList | e
 * MaybeBrackets: brackets | e
 * Callable: callableType MaybeGenericConstraints OptionalCallableDefinition
 * OptionalCallableDefinition: lparen TypeList rparen OptionalReturnType | e
 * OptionalReturnType: colon UnionType | e
 */
final class TypeParser
{
    const TOK_PIPE = '|';
    const TOK_QMARK = '?';
    const TOK_LT = '<';
    const TOK_GT = '>';
    const TOK_COMMA = ',';
    const TOK_TYPE = 'type';
    const TOK_CALLABLE_TYPE = 'callable|Closure';
    const TOK_BRACKETS = '[]';
    const TOK_WS = 'whitespace';
    const TOK_COLON = ':';
    const TOK_LPAREN = '(';
    const TOK_RPAREN = ')';

    public static function fromString(?string $input): UnionType {
        return $input ? (new self())->parse($input) : UnionType::empty();
    }

    public function parse(string $input): UnionType {
        $tokenStream = tokenStreamLexer(skipLexer(lexer([
            '/\|/A' => self::TOK_PIPE,
            '/\?/A' => self::TOK_QMARK,
            '/</A' => self::TOK_LT,
            '/>/A' => self::TOK_GT,
            '/,/A' => self::TOK_COMMA,
            '/:/A' => self::TOK_COLON,
            '/\(/A' => self::TOK_LPAREN,
            '/\)/A' => self::TOK_RPAREN,
            '/callable|\\\?Closure/A' => self::TOK_CALLABLE_TYPE,
            '/(\\\?[a-zA-Z_][a-zA-Z0-9_]*)+/A' => self::TOK_TYPE,
            '/\[\]/A' => self::TOK_BRACKETS,
            '/\s+/A' => self::TOK_WS,
        ]), [self::TOK_WS]))($input);

        $unionType = $this->parseUnionType($tokenStream);
        if (!$tokenStream->isEmpty()) {
            throw new ParseException('Expected eof, got: ' . $tokenStream->getToken()->token);
        }

        return $unionType;
    }

    private function parseUnionType(TokenStream $toks): UnionType {
        $atomicTypes = [$this->parseAtomicType($toks)];
        $nextTok = $toks->peek();
        while ($nextTok !== null && $nextTok->token === self::TOK_PIPE) {
            $toks->getToken();
            $atomicTypes[] = $this->parseAtomicType($toks);
            $nextTok = $toks->peek();
        }

        return new UnionType($atomicTypes);
    }

    private function parseAtomicType(TokenStream $toks): AtomicType {
        $isNullable = $this->parseNullable($toks);
        $typeDefinitionTok = $this->expectTokens($toks, [self::TOK_TYPE, self::TOK_CALLABLE_TYPE], 'atomic type');
        $genericTypeConstraints = $this->parseMaybeGenericConstraints($toks);
        $callableDefinition = $typeDefinitionTok->token === self::TOK_CALLABLE_TYPE ? $this->parseOptionalCallableDefinition($toks) : null;
        $isArray = $this->parseMaybeBrackets($toks);
        return new AtomicType($typeDefinitionTok->match, $genericTypeConstraints, $isNullable, $isArray, $callableDefinition);
    }

    private function parseNullable(TokenStream $toks): bool {
        return $this->peekAndConsumeOnMatchToken($toks, self::TOK_QMARK) !== null;
    }

    private function parseOptionalCallableDefinition(TokenStream $toks): ?CallableDefinition {
        if ($this->peekAndConsumeOnMatchToken($toks, self::TOK_LPAREN) === null) {
            return null;
        }

        $paramTypes = $this->parseTypeList($toks, self::TOK_RPAREN);
        if ($this->peekAndConsumeOnMatchToken($toks, self::TOK_COLON) === null) {
            return new CallableDefinition($paramTypes);
        }
        $returnType = $this->parseUnionType($toks);
        return new CallableDefinition($paramTypes, $returnType);
    }

    /** @return UnionType[] */
    private function parseMaybeGenericConstraints(TokenStream $toks): array {
        if ($this->peekAndConsumeOnMatchToken($toks, self::TOK_LT) === null) {
            return [];
        }

        $unionTypes = $this->parseTypeList($toks, self::TOK_GT);
        if (!count($unionTypes)) {
            throw new ParseException('Generic constraints cannot be empty.');
        }
        return $unionTypes;
    }

    /** @return UnionType[] */
    private function parseTypeList(TokenStream $toks, string $stopToken): array {
        $types = [];
        $nextTok = $toks->peek();
        while ($nextTok !== null && $nextTok->token !== $stopToken) {
            if (count($types) > 0) {
                if ($nextTok->token !== self::TOK_COMMA) {
                    throw new ParseException('Expected token comma for argument list, got: ' . $this->tokToString($nextTok));
                }
                $toks->getToken();
            }

            $types[] = $this->parseUnionType($toks);
            $nextTok = $toks->peek();
        }
        if ($nextTok === null || $nextTok->token !== $stopToken) {
            throw new ParseException("Expected token {$stopToken} for argument list, got: " . $this->tokToString($nextTok));
        }
        $toks->getToken();
        return $types;
    }

    private function parseMaybeBrackets(TokenStream $toks): bool {
        return $this->peekAndConsumeOnMatchToken($toks, self::TOK_BRACKETS) !== null;
    }

    private function tokToString(?MatchedToken $tok): string {
        return $tok ? $tok->token : 'eof';
    }

    private function expectTokens(TokenStream $toks, array $tokens, string $for): MatchedToken {
        $tok = $toks->getToken();
        if (!$tok || !in_array($tok->token, $tokens)) {
            $tokensStr = implode(', ', $tokens);
            throw new ParseException("Expected token {$tokensStr} for {$for}, got: " . $this->tokToString($tok));
        }
        return $tok;
    }

    private function peekAndConsumeOnMatchToken(TokenStream $toks, string $token): ?MatchedToken {
        $tok = $toks->peek();
        if (!$tok || $tok->token !== $token) {
            return null;
        }
        $toks->getToken();
        return $tok;
    }
}
